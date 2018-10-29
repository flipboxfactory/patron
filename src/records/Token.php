<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\validators\DateTimeValidator;
use DateTime;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\ember\records\ActiveRecordWithId;
use flipbox\ember\records\traits\StateAttribute;
use flipbox\patron\db\TokenActiveQuery;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property string $accessToken
 * @property string $refreshToken
 * @property DateTime|null $dateExpires
 * @property array $values
 * @property ProviderInstance[] $instances
 * @property TokenEnvironment[] $environments
 */
class Token extends ActiveRecordWithId
{
    use StateAttribute,
        traits\ProviderAttribute,
        traits\RelatedEnvironmentsAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'patron_tokens';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = [
        'providerId'
    ];

    /*******************************************
     * QUERY
     *******************************************/

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

        return $this->beforeSaveEnvironments($insert);
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

        return $this->insertInternalEnvironments($attributes) ? $response : false;
    }

    /*******************************************
     * ENVIRONMENTS
     *******************************************/

    /**
     * @inheritdoc
     */
    protected static function environmentRecordClass(): string
    {
        return TokenEnvironment::class;
    }

    /**
     * @inheritdoc
     */
    protected function prepareEnvironmentRecordConfig(array $config = []): array
    {
        $config['token'] = $this;
        return $config;
    }

    /**
     * @inheritdoc
     */
    protected function environmentRelationshipQuery(): ActiveQueryInterface
    {
        return $this->hasMany(
            static::environmentRecordClass(),
            ['tokenId' => 'id']
        );
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
     * Get all of the associated instances.
     *
     * @param array $config
     * @return \yii\db\ActiveQuery
     */
    public function getInstances(array $config = [])
    {
        $query = $this->hasMany(
            ProviderInstance::class,
            ['providerId' => 'providerId']
        );

        if (!empty($config)) {
            QueryHelper::configure(
                $query,
                $config
            );
        }

        return $query;
    }
}
