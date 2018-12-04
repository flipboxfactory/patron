<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use flipbox\craft\ember\helpers\ModelHelper;

/**
 * @property int|null $tokenId
 * @property Token|null $token
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait TokenRulesTrait
{
    /**
     * @return array
     */
    protected function tokenRules(): array
    {
        return [
            [
                [
                    'tokenId'
                ],
                'number',
                'integerOnly' => true
            ],
            [
                [
                    'tokenId',
                    'token'
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_DEFAULT
                ]
            ]
        ];
    }
}
