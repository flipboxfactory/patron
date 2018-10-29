<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use Craft;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\records\ActiveRecordWithId;
use flipbox\patron\db\ProviderInstanceActiveQuery;
use flipbox\patron\helpers\ProviderHelper;
use flipbox\patron\Patron;
use flipbox\patron\providers\SettingsInterface;
use flipbox\patron\validators\ProviderSettings as ProviderSettingsValidator;
use yii\db\ActiveQueryInterface;

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
    use traits\ProviderAttribute,
        traits\RelatedEnvironmentsAttribute;

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
     * @return ProviderInstanceActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Craft::createObject(ProviderInstanceActiveQuery::class, [get_called_class()]);
    }

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


    /*******************************************
     * EVENTS
     *******************************************/

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

        if (!parent::beforeSave($insert)) {
            return false;
        }

        return $this->beforeSaveEnvironments($insert);
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
     * @inheritdoc
     * @throws \Throwable
     */
    protected function insertInternal($attributes = null)
    {
        if (!parent::insertInternal($attributes)) {
            return false;
        }

        return $this->insertInternalEnvironments($attributes);
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

        return $this->upsertEnvironmentsInternal($attributes) ? $response : false;
    }


    /*******************************************
     * ENVIRONMENTS
     *******************************************/

    /**
     * @inheritdoc
     */
    protected static function environmentRecordClass(): string
    {
        return ProviderEnvironment::class;
    }

    /**
     * @inheritdoc
     */
    protected function prepareEnvironmentRecordConfig(array $config = []): array
    {
        $config['instance'] = $this;
        return $config;
    }

    /**
     * @inheritdoc
     */
    protected function environmentRelationshipQuery(): ActiveQueryInterface
    {
        return $this->hasMany(
            static::environmentRecordClass(),
            ['instanceId' => 'id']
        );
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
