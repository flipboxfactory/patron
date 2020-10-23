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
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\ember\records\ActiveRecordWithId;
use flipbox\craft\ember\records\StateAttributeTrait;
use flipbox\patron\queries\TokenActiveQuery;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property string $accessToken
 * @property string $refreshToken
 * @property DateTime|null $dateExpires
 * @property array $values
 */
class Token extends ActiveRecordWithId
{
    use StateAttributeTrait,
        ProviderAttributeTrait;

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
     * PROJECT CONFIG
     *******************************************/

    /**
     * Return an array suitable for Craft's Project config
     */
    public function toProjectConfig(): array
    {
        return $this->toArray([
            'accessToken',
            'refreshToken',
            'providerId',
            'values',
            'enabled',
            'dateUpdated'
        ]);
    }
}
