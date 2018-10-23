<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\helpers\ObjectHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\ember\records\ActiveRecordWithId;
use flipbox\patron\helpers\ProviderHelper;
use flipbox\patron\Patron;
use flipbox\patron\providers\SettingsInterface;
use flipbox\patron\validators\ProviderSettings as ProviderSettingsValidator;
use yii\base\InvalidArgumentException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property string $clientId
 * @property string $clientSecret
 * @property array $settings
 * @property int $providerId
 * @property Provider $provider
 * @property ProviderEnvironment[] $environments
 */
class ProviderInstance extends ActiveRecordWithId
{
    use traits\ProviderAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'patron_provider_instances';

    /**
     * The length of the identifier
     */
    const CLIENT_ID_LENGTH = 100;

    /**
     * The length of the secret
     */
    const CLIENT_SECRET_LENGTH = 255;

    /**
     * @var bool
     */
    public $autoSaveEnvironments = true;

    /**
     * @var SettingsInterface
     */
    private $providerSettings;

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = [
        'providerId'
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->providerRules(),
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
                        'providerId',
                        'clientId'
                    ],
                    'required'
                ],
                [
                    [
                        'settings'
                    ],
                    ProviderSettingsValidator::class
                ],
                [
                    [
                        'clientId',
                        'clientSecret',
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
     * Get all of the associated environments.
     *
     * @param array $config
     * @return \yii\db\ActiveQueryInterface
     */
    public function getEnvironments(array $config = [])
    {
        $query = $this->hasMany(
            ProviderEnvironment::class,
            ['settingsId' => 'id']
        )
            ->indexBy('environment');

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
        if ($environment instanceof ProviderEnvironment) {
            return $environment;
        }

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

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        if ($this->clientSecret) {
            $this->clientSecret = ProviderHelper::decryptClientSecret($this->clientSecret);
        }

        parent::afterFind();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->clientSecret) {
            $this->clientSecret = ProviderHelper::encryptClientSecret($this->clientSecret);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->clientSecret) {
            $this->clientSecret = ProviderHelper::decryptClientSecret($this->clientSecret);
        }

        parent::afterSave($insert, $changedAttributes);
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
        $environments = $this->environments;

        if (!parent::insertInternal($attributes)) {
            return false;
        }

        $this->setEnvironments($environments);

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

        /** @var ProviderEnvironment[] $allRecords */
        $allRecords = $this->getEnvironments()
            ->indexBy('environment')
            ->all();


        foreach ($this->environments as $model) {
            ArrayHelper::remove($allRecords, $model->environment);
            $model->settingsId = $this->getId();

            if (!$model->save()) {
                $successful = false;

                $error = Craft::t(
                    'patron',
                    "Couldn't save environment due to validation errors:"
                );

                foreach ($model->getFirstErrors() as $attributeError) {
                    $error .= "\n- " . Craft::t('patron', $attributeError);
                }

                $this->addError('environments', $error);
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
     * @throws \yii\base\InvalidConfigException
     */
    public function getHtml(): string
    {
        return $this->getProviderSettings()->inputHtml();
    }

    /**
     * @return SettingsInterface
     * @throws \yii\base\InvalidConfigException
     */
    public function getProviderSettings(): SettingsInterface
    {
        if (!$this->providerSettings instanceof SettingsInterface) {
            $this->providerSettings = Patron::getInstance()->getProviderSettings()->resolveSettings(
                $this->getProvider(),
                $this->settings
            );
        }

        return $this->providerSettings;
    }
}
