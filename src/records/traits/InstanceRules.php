<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records\traits;

use flipbox\ember\helpers\ModelHelper;
use flipbox\patron\records\ProviderInstance;

/**
 * @property int|null $instanceId
 * @property ProviderInstance|null $instance
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait InstanceRules
{
    /**
     * @return array
     */
    protected function instanceRules(): array
    {
        return [
            [
                [
                    'instanceId'
                ],
                'number',
                'integerOnly' => true
            ],
            [
                [
                    'instanceId',
                    'instance'
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_DEFAULT
                ]
            ]
        ];
    }
}
