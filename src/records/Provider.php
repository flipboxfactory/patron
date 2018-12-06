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
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\ember\helpers\ObjectHelper;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\craft\ember\models\HandleRulesTrait;
use flipbox\craft\ember\records\ActiveRecordWithId;
use flipbox\craft\ember\records\StateAttributeTrait;
use flipbox\patron\helpers\ProviderHelper;
use flipbox\patron\Patron;
use flipbox\patron\queries\ProviderActiveQuery;
use flipbox\patron\validators\ProviderValidator;
use yii\helpers\ArrayHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property string $class
 * @property ProviderLock[] $locks
 * @property Token[] $tokens
 * @property ProviderInstance[] $instances
 * @property ProviderEnvironment[] $environments
 */
class Provider extends ActiveRecordWithId
{
    use HandleRulesTrait,
        StateAttributeTrait;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'patron_providers';

    /**
     * @deprecated
     */
    const CLIENT_ID_LENGTH = ProviderInstance::CLIENT_ID_LENGTH;

    /**
     * @deprecated
     */
    const CLIENT_SECRET_LENGTH = ProviderInstance::CLIENT_SECRET_LENGTH;

    /**
     * @var bool
     */
    public $autoSaveInstances = false;

    /**
     * Environments that are temporarily set during the save process
     *
     * @var null|array
     */
    private $insertInstances;

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
                        'class'
                    ],
                    ProviderValidator::class
                ],
                [
                    [
                        'class'
                    ],
                    'required'
                ],
                [
                    [
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
     * Get all of the associated instances.
     *
     * @param array $config
     * @return \yii\db\ActiveQuery
     */
    public function getInstances(array $config = [])
    {
        $query = $this->hasMany(
            ProviderInstance::class,
            ['providerId' => 'id']
        )
            ->indexBy('id');

        if (!empty($config)) {
            QueryHelper::configure(
                $query,
                $config
            );
        }

        return $query;
    }

    /**
     * @param array $instances
     * @return $this
     */
    public function setInstances(array $instances = [])
    {
        $records = [];
        foreach (array_filter($instances) as $environment) {
            $records[] = $this->resolveInstance($environment);
        }

        $this->populateRelation('instances', $records);
        return $this;
    }

    /**
     * @param $instance
     * @return ProviderInstance
     */
    protected function resolveInstance($instance): ProviderInstance
    {
        if ($instance instanceof ProviderInstance) {
            return $instance;
        }

        $record = new ProviderInstance();

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ObjectHelper::populate(
            $record,
            $instance
        );
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
            ['instanceId' => 'id']
        )
            ->via('instances')
            ->indexBy('environment');

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
     * EVENTS
     *******************************************/

    /**
     * @inheritdoc
     */
    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert !== true ||
            $this->isRelationPopulated('instances') !== true ||
            $this->autoSaveInstances !== true
        ) {
            return true;
        }

        $this->insertInstances = $this->instances;

        return true;
    }

    /*******************************************
     * UPDATE / INSERT
     *******************************************/

    /**
     * We're extracting the environments that may have been explicitly set on the record.  When the 'id'
     * attribute is updated, it removes any associated relationships.
     *
     * @inheritdoc
     * @throws \Throwable
     */
    protected function insertInternal($attributes = null)
    {
        if (!parent::insertInternal($attributes)) {
            return false;
        }

        if (null === $this->insertInstances) {
            return true;
        }

        $this->setInstances($this->insertInstances);
        $this->insertInstances = null;

        return $this->upsertInternal($attributes);
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    protected function updateInternal($attributes = null)
    {
        if (false === ($response = parent::updateInternal($attributes))) {
            return false;
        }

        return $this->upsertInternal($attributes) ? $response : false;
    }

    /**
     * @param null $attributes
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    protected function upsertInternal($attributes = null): bool
    {
        if (empty($attributes)) {
            return $this->saveInstances();
        }

        if (array_key_exists('instances', $attributes)) {
            return $this->saveInstances(true);
        }

        return true;
    }

    /**
     * @param bool $force
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    protected function saveInstances(bool $force = false): bool
    {
        if ($force === false && $this->autoSaveInstances !== true) {
            return true;
        }

        $successful = true;

        /** @var ProviderInstance[] $allRecords */
        $allRecords = $this->getInstances()
            ->all();

        ArrayHelper::index($allRecords, 'providerId');

        foreach ($this->instances as $model) {
            ArrayHelper::remove($allRecords, $this->getId());
            $model->providerId = $this->getId();

            if (!$model->save()) {
                $successful = false;
                // Log the errors
                $error = Craft::t(
                    'patron',
                    "Couldn't save instance due to validation errors:"
                );
                foreach ($model->getFirstErrors() as $attributeError) {
                    $error .= "\n- " . Craft::t('patron', $attributeError);
                }

                $this->addError('instances', $error);
            }
        }

        // Delete old records
        foreach ($allRecords as $record) {
            $record->delete();
        }

        return $successful;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getDisplayName(): string
    {
        return ProviderHelper::displayName(
            $this->class
        );
    }
}
