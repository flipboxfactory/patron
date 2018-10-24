<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\validators\DateTimeValidator;
use DateTime;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\helpers\ObjectHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\ember\records\ActiveRecordWithId;
use flipbox\ember\records\traits\StateAttribute;
use flipbox\patron\db\TokenActiveQuery;
use flipbox\patron\Patron;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property string $accessToken
 * @property string $refreshToken
 * @property DateTime|null $dateExpires
 * @property array $values
 * @property TokenEnvironment[] $environments
 */
class Token extends ActiveRecordWithId
{
    use StateAttribute,
        traits\ProviderAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'patron_tokens';

    /**
     * @var bool
     */
    public $autoSaveEnvironments = true;

    /**
     * Environments that are temporarily set during the save process
     *
     * @var null|array
     */
    private $insertEnvironments;

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = [
        'providerId'
    ];

    /**
     * @inheritdoc
     * @return TokenActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Craft::createObject(TokenActiveQuery::class, [get_called_class()]);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isEnabled() && !$this->hasExpired();
    }

    /**
     * @return bool
     */
    public function hasExpired(): bool
    {
        $dateExpires = $this->dateExpires ?: new DateTime('now');
        return DateTimeHelper::isInThePast($dateExpires);
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
            $this->isRelationPopulated('environments') !== true ||
            $this->autoSaveEnvironments !== true
        ) {
            return true;
        }

        $this->insertEnvironments = $this->environments;

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

        if (null === $this->insertEnvironments) {
            return true;
        }

        $this->setEnvironments($this->insertEnvironments);
        $this->insertEnvironments = null;

        return $this->upsertInternal($attributes);
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    protected function updateInternal($attributes = null)
    {
        if (!parent::updateInternal($attributes)) {
            return false;
        }

        return $this->upsertInternal($attributes);
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
            return $this->saveEnvironments();
        }

        if (array_key_exists('environments', $attributes)) {
            return $this->saveEnvironments(true);
        }

        return true;
    }

    /*******************************************
     * ENVIRONMENTS
     *******************************************/

    /**
     * @param bool $force
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    protected function saveEnvironments(bool $force = false): bool
    {
        if ($force === false && $this->autoSaveEnvironments !== true) {
            return true;
        }

        $successful = true;

        /** @var TokenEnvironment[] $allRecords */
        $allRecords = $this->getEnvironments()
            ->indexBy('environment')
            ->all();

        foreach ($this->environments as $model) {
            ArrayHelper::remove($allRecords, $model->environment);
            $model->tokenId = $this->getId();

            if (!$model->save()) {
                $successful = false;

                $error = Craft::t(
                    'patron',
                    "Couldn't save environment due to validation errors:"
                );
                foreach ($model->getFirstErrors() as $attributeError) {
                    $error .= "\n- " . Craft::t('patron', $attributeError);
                }

                $this->addError('sites', $error);
            }
        }

        // Delete old records
        foreach ($allRecords as $record) {
            $record->delete();
        }

        return $successful;
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->stateRules(),
            $this->providerRules(),
            [
                [
                    [
                        'accessToken',
                        'refreshToken'
                    ],
                    'unique'
                ],
                [
                    [
                        'dateExpires'
                    ],
                    DateTimeValidator::class
                ],
                [
                    [
                        'providerId',
                        'accessToken'
                    ],
                    'required'
                ],
                [
                    [
                        'accessToken',
                        'values',
                        'dateExpires'
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
     * Get all of the associated environments.
     *
     * @param array $config
     * @return \yii\db\ActiveQuery
     */
    public function getEnvironments(array $config = [])
    {
        $query = $this->hasMany(
            TokenEnvironment::class,
            ['tokenId' => 'id']
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
        $environments = array_filter($environments);

        // Do nothing
        if (empty($environments) && !$this->isRelationPopulated('environments')) {
            return $this;
        }

        $records = [];
        foreach (array_filter($environments) as $key => $environment) {
            $records[$key] = $this->resolveEnvironment($key, $environment);
        }

        $this->populateRelation('environments', $records);
        return $this;
    }

    /**
     * @param string $key
     * @param $environment
     * @return TokenEnvironment
     */
    protected function resolveEnvironment(string $key, $environment): TokenEnvironment
    {
        if ($environment instanceof TokenEnvironment) {
            return $environment;
        }

        if (!$record = $this->environments[$key] ?? null) {
            $record = new TokenEnvironment();
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
}
