<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\ember\records\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $tokenId
 * @property string $environment
 */
class TokenEnvironment extends ActiveRecord
{
    use traits\EnvironmentAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'patron_token_environments';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->environmentRules(),
            [
                [
                    [
                        'tokenId'
                    ],
                    'number',
                    'integerOnly' => true
                ],
                [
                    [
                        'tokenId'
                    ],
                    'required'
                ],
                [
                    [
                        'tokenId'
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
     * Get the associated token
     *
     * @param array $config
     * @return ActiveQueryInterface
     */
    public function getProvider(array $config = [])
    {
        $query = $this->hasOne(
            Token::class,
            ['tokenId' => 'id']
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
