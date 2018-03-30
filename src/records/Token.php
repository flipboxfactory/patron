<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\validators\DateTimeValidator;
use DateTime;
use flipbox\ember\helpers\ModelHelper;
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
     */
    public static function find()
    {
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

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // Prepare date value
        $this->prepareDateExpires();

        return parent::beforeSave($insert);
    }

    /**
     * Prepare date value for storage
     * @return void
     */
    protected function prepareDateExpires()
    {
        $this->dateExpires = Db::prepareDateForDb($this->dateExpires);
    }

    /**
     * Get the associated Authorization
     *
     * @return ActiveQueryInterface
     */
    public function getProvider()
    {
        return $this->hasOne(
            Provider::class,
            ['providerId' => 'id']
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
            [
                //                [
                //                    [
                //                        'accessToken',
                //                        'refreshToken'
                //                    ],
                //                    TokenValidator::class
                //                ],
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
                        'dateExpires'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ],
                [
                    [
                        'providerId'
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
                        'accessToken'
                    ],
                    'required'
                ],
                [
                    [
                        'providerId',
                        'accessToken',
                        'values'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }
}
