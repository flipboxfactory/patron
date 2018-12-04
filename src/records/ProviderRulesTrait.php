<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use flipbox\craft\ember\helpers\ModelHelper;

/**
 * @property int|null $providerId
 * @property Provider|null $provider
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait ProviderRulesTrait
{
    /**
     * @return array
     */
    protected function providerRules(): array
    {
        return [
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
                    'provider'
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_DEFAULT
                ]
            ]
        ];
    }
}
