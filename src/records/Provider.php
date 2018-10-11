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
use craft\helpers\StringHelper;
use craft\helpers\Template;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\helpers\ObjectHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\ember\records\ActiveRecordWithId;
use flipbox\ember\records\traits\StateAttribute;
use flipbox\ember\traits\HandleRules;
use flipbox\patron\db\ProviderActiveQuery;
use flipbox\patron\helpers\ProviderHelper;
use flipbox\patron\Patron;
use flipbox\patron\providers\SettingsInterface;
use flipbox\patron\validators\ProviderValidator;
use Twig_Markup;
use yii\helpers\ArrayHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property string $clientId
 * @property string $clientSecret
 * @property string $class
 * @property array $settings
 * @property ProviderLock[] $locks
 * @property Token[] $tokens
 * @property ProviderEnvironment[] $environments
 */
class Provider extends ActiveRecordWithId
{
    use HandleRules,
        StateAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'patron_providers';

    /**
     * @var SettingsInterface
     */
    private $settingsModel;

    /**
     * The length of the identifier
     */
    const CLIENT_ID_LENGTH = 100;

    /**
     * The length of the secret
     */
    const CLIENT_SECRET_LENGTH = 100;

    /**
     * @return string|null
     */
    public function getIcon()
    {
        if ($this->class === null) {
            return null;
        }

        return Patron::getInstance()->getCp()->getProviderIcon(
            $this->class
        );
    }

    /**
     * @inheritdoc
     * @return ProviderActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Craft::createObject(ProviderActiveQuery::class, [get_called_class()]);
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
                        'clientId',
                        'class'
                    ],
                    'required'
                ],
                [
                    [
                        'clientId',
                        'clientSecret',
                        'class',
                        'settings'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }

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

    /**
     * Get all of the associated environments.
     *
     * @param array $config
     * @return \yii\db\ActiveQuery
     */
    public function getEnvironments(array $config = [])
    {
        $query = $this->hasMany(
            ProviderEnvironment::class,
            ['providerId' => 'id']
        )->indexBy('environment');

        if (!empty($config)) {
            QueryHelper::configure(
                $query,
                $config
            );
        }

        return $query;
    }

    /**
     * @param array $environments
     * @return $this
     */
    public function setEnvironments(array $environments = [])
    {
        $records = [];
        foreach (array_filter($environments) as $key => $environment) {
            $records[] = $this->resolveEnvironment($key, $environment);
        }

        $this->populateRelation('environments', $records);
        return $this;
    }

    /**
     * @param string $key
     * @param $environment
     * @return ProviderEnvironment
     */
    protected function resolveEnvironment(string $key, $environment): ProviderEnvironment
    {
        if (!$record = $this->environments[$key] ?? null) {
            $record = new ProviderEnvironment();
        }

        if (!is_array($environment)) {
            $environment = ['environment' => $environment];
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ObjectHelper::populate(
            $record,
            $environment
        );
    }

    /*******************************************
     * SAVE
     *******************************************/

    /**
     * @param PluginInterface $plugin
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
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
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function addLock(PluginInterface $plugin): bool
    {
        if (null === ($pluginId = $this->getPluginId($plugin))) {
            return false;
        }

        return Patron::getInstance()->getProviderLocks()->associateByIds(
            $provider->getId(),
            $pluginId
        );
    }

    /**
     * @param PluginInterface $plugin
     * @return bool
     */
    public function removeLock(PluginInterface $plugin): bool
    {
        if (null === ($pluginId = $this->getPluginId($plugin))) {
            return false;
        }

        return Patron::getInstance()->getProviderLocks()->dissociateByIds(
            $provider->getId(),
            $pluginId
        );
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
     * EVENTS
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
                    'id' => ArrayHelper::getColumn($locks, ['pluginId']),
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

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    public function afterSave($insert, $changedAttributes)
    {
        Patron::getInstance()->manageProviders()->saveEnvironments($this);
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return ProviderHelper::displayName(
            $this->class
        );
    }

    /**
     * @return Twig_Markup
     * @throws \yii\base\InvalidConfigException
     */
    public function getSettingsHtml(): Twig_Markup
    {
        return Template::raw(
            $this->getSettingsModel()->inputHtml()
        );
    }

    /**
     * @return SettingsInterface
     * @throws \yii\base\InvalidConfigException
     */
    protected function getSettingsModel(): SettingsInterface
    {
        if (!$this->settingsModel instanceof SettingsInterface) {
            $this->settingsModel = Patron::getInstance()->manageProviders()->resolveSettings($this, $this->settings);
        }

        return $this->settingsModel;
    }
}
