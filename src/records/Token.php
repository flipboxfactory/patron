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
    use StateAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'patron_tokens';

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
     * @throws \Throwable
     */
    public function afterSave($insert, $changedAttributes)
    {
        Patron::getInstance()->manageTokens()->saveEnvironments($this);
        parent::afterSave($insert, $changedAttributes);
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->stateRules(),
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
                        'providerId'
                    ],
                    'number',
                    'integerOnly' => true
                ],
                [
                    [
                        'providerId',
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
     * Get the associated Authorization
     *
     * @param array $config
     * @return ActiveQueryInterface
     */
    public function getProvider(array $config = [])
    {
        $query = $this->hasOne(
            Provider::class,
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
     * @return TokenEnvironment
     */
    protected function resolveEnvironment(string $key, $environment): TokenEnvironment
    {
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
