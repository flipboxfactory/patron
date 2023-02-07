<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use Craft;
use craft\base\PluginInterface;
use craft\db\Query;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use flipbox\craft\ember\helpers\ObjectHelper;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\craft\ember\models\HandleRulesTrait;
use flipbox\craft\ember\records\ActiveRecordWithId;
use flipbox\craft\ember\records\StateAttributeTrait;
use flipbox\patron\events\RegisterProviderSettings;
use flipbox\patron\helpers\ProviderHelper;
use flipbox\patron\Patron;
use flipbox\patron\queries\ProviderActiveQuery;
use flipbox\patron\settings\BaseSettings;
use flipbox\patron\settings\SettingsInterface;
use flipbox\patron\validators\ProviderSettings as ProviderSettingsValidator;
use flipbox\patron\validators\ProviderValidator;
use yii\helpers\ArrayHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property string $clientId
 * @property string $clientSecret
 * @property string $class
 * @property ProviderLock[] $locks
 * @property Token[] $tokens
 * @property SettingsInterface $settings
 */
class Provider extends ActiveRecordWithId
{
    use HandleRulesTrait,
        StateAttributeTrait;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'patron_providers';

    const CLIENT_ID_LENGTH = 100;
    const CLIENT_SECRET_LENGTH = 255;

    protected $getterPriorityAttributes = ['settings'];

    /**
     * @inheritdoc
     * @return ProviderActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public static function find(): \craft\db\ActiveQuery
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Craft::createObject(ProviderActiveQuery::class, [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    protected static function findByCondition($condition)
    {
        if (!is_numeric($condition) && is_string($condition)) {
            $condition = ['handle' => $condition];
        }

        /** @noinspection PhpInternalEntityUsedInspection */
        return parent::findByCondition($condition);
    }

    /**
     * An array of additional information about the provider.  As an example:
     *
     * ```
     * [
     *      'name' => 'Provider Name',
     *      'icon' => '/path/to/icon.svg'
     * ]
     * ```
     * @return array
     * @throws \ReflectionException
     */
    public function getInfo(): array
    {
        if ($this->class === null) {
            return [];
        }

        $info = Patron::getInstance()->getCp()->getProviderInfo();

        return $info[$this->class] ?? [
            'name' => ProviderHelper::displayName($this->class)
        ];
    }

    /**
     * @return SettingsInterface
     * @throws \yii\base\InvalidConfigException
     */
    public function getSettings(): SettingsInterface
    {
        $settings = $this->getAttribute('settings');
        if (!$settings instanceof SettingsInterface) {
            $settings = Patron::getInstance()->providerSettings(
                $this->class,
                $this->getAttribute('settings')
            );

            $this->setAttribute('settings', $settings);
        }

        return $settings;
    }


    /**
     * @param $record
     * @param $row
     */
    public static function populateRecord($record, $row)
    {
        // Apply override settings
        if (null !== ($handle = $row['handle'] ?? null)) {
            $row = array_merge(
                $row,
                Patron::getInstance()->getSettings()->getProvider($handle)
            );
        }

        parent::populateRecord($record, $row);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->handleRules(),
            $this->stateRules(),
            [
                [
                    [
                        'clientId'
                    ],
                    'string',
                    'max' => static::CLIENT_ID_LENGTH
                ],
                [
                    [
                        'clientSecret'
                    ],
                    'string',
                    'max' => static::CLIENT_SECRET_LENGTH
                ],
                [
                    [
                        'class'
                    ],
                    ProviderValidator::class
                ],
                [
                    [
                        'settings'
                    ],
                    ProviderSettingsValidator::class
                ],
                [
                    [
                        'class',
                        'clientId'
                    ],
                    'required'
                ],
                [
                    [
                        'class',
                        'clientId',
                        'clientSecret',
                        'settings',
                    ],
                    'safe',
                    'on' => [
                        self::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }


    /*******************************************
     * RELATIONS
     *******************************************/

    /**
     * Get all of the associated tokens.
     *
     * @param array $config
     * @return \yii\db\ActiveQuery
     */
    public function getTokens(array $config = [])
    {
        $query = $this->hasMany(
            Token::class,
            ['providerId' => 'id']
        );

        if (!empty($config)) {
            QueryHelper::configure(
                $query,
                $config
            );
        }

        return $query;
    }

    /**
     * Get all of the associated tokens.
     *
     * @param array $config
     * @return \yii\db\ActiveQuery
     */
    public function getLocks(array $config = [])
    {
        $query = $this->hasMany(
            ProviderLock::class,
            ['providerId' => 'id']
        );

        if (!empty($config)) {
            QueryHelper::configure(
                $query,
                $config
            );
        }

        return $query;
    }

    /*******************************************
     * SAVE
     *******************************************/

    /**
     * @param PluginInterface $plugin
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     */
    public function saveAndLock(PluginInterface $plugin, $runValidation = true, $attributeNames = null): bool
    {
        if (!$this->save($runValidation, $attributeNames)) {
            return false;
        }

        return $this->addLock($plugin);
    }


    /*******************************************
     * LOCK
     *******************************************/

    /**
     * @param PluginInterface $plugin
     * @return bool
     */
    public function addLock(PluginInterface $plugin): bool
    {
        if (null === ($pluginId = $this->getPluginId($plugin))) {
            return false;
        }

        $record = ProviderLock::findOne([
            'providerId' => $this->getId(),
            'pluginId' => $pluginId
        ]);

        if (!empty($record)) {
            return true;
        }

        $record = new ProviderLock();

        $record->setAttributes([
            'providerId' => $this->getId(),
            'pluginId' => $pluginId
        ]);

        return (bool)$record->save();
    }

    /**
     * @param PluginInterface $plugin
     * @return bool
     * @throws \Throwable
     */
    public function removeLock(PluginInterface $plugin): bool
    {
        if (null === ($pluginId = $this->getPluginId($plugin))) {
            return false;
        }

        if (null === ($record = ProviderLock::findOne([
                'providerId' => $this->getId() ?: 0,
                'pluginId' => $pluginId
            ]))) {
            return true;
        }

        return (bool)$record->delete();
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return !empty($this->locks);
    }

    /**
     * @param PluginInterface $plugin
     * @return int|null
     */
    protected function getPluginId(PluginInterface $plugin)
    {
        $id = (new Query())
            ->select([
                'id',
            ])
            ->from(['{{%plugins}}'])
            ->where([
                'handle' => $plugin->getHandle()
            ])
            ->scalar();

        return $id ? (int)$id : null;
    }

    /**
     * @param PluginInterface $plugin
     * @return int|null
     */
    protected function getPluginName(PluginInterface $plugin)
    {
        $id = (new Query())
            ->select([
                'id',
            ])
            ->from(['{{%plugins}}'])
            ->where([
                'handle' => $plugin->getHandle()
            ])
            ->scalar();

        return $id ? (int)$id : null;
    }


    /*******************************************
     * DELETE
     *******************************************/

    /**
     * @param PluginInterface|null $plugin
     * @return bool|false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(PluginInterface $plugin = null)
    {
        return $this->canDelete($plugin) ? parent::delete() : false;
    }

    /**
     * @param PluginInterface|null $plugin
     * @return bool
     * @throws \craft\errors\InvalidPluginException
     */
    protected function canDelete(PluginInterface $plugin = null)
    {
        // If a plugin is locking this, prevent deletion
        $lockQuery = $this->getLocks();
        if (null !== $plugin) {
            $lockQuery->andWhere(
                ['<>', 'pluginId', $this->getPluginId($plugin)]
            );
        }

        $locks = $lockQuery->all();

        if (count($locks) > 0) {
            $handles = (new Query())
                ->select([
                    'handle',
                ])
                ->from(['{{%plugins}}'])
                ->where([
                    'id' => ArrayHelper::getColumn($locks, 'pluginId'),
                ])
                ->column();

            $names = [];
            foreach ($handles as $handle) {
                $plugin = Craft::$app->getPlugins()->getPluginInfo($handle);
                $names[] = $plugin['name'] ?? 'Unknown Plugin';
            }

            $this->addError(
                'locks',
                Craft::t(
                    'patron',
                    'The provider is locked by the following plugins: {plugins}',
                    [
                        'plugins' => StringHelper::toString($names, ', ')
                    ]
                )
            );
            return false;
        }

        return true;
    }


    /*******************************************
     * PROJECT CONFIG
     *******************************************/

    /**
     * Return an array suitable for Craft's Project config
     */
    public function toProjectConfig(): array
    {
        return $this->toArray([
            'handle',
            'clientId',
            'clientSecret',
            'class',
            'scopes',
            'enabled',
            'dateUpdated'
        ]);
    }
}
