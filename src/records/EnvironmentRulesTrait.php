<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\patron\Patron;

/**
 * @property string|null $environment
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait EnvironmentRulesTrait
{
    /**
     * @inheritdoc
     */
    protected function environmentRules(): array
    {
        return [
            [
                [
                    'environment'
                ],
                'required'
            ],
            [
                [
                    'environment'
                ],
                'default',
                'value' => Patron::getInstance()->getSettings()->getEnvironment()
            ],
            [
                [
                    'environment'
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_DEFAULT
                ]
            ]
        ];
    }
}
