<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use flipbox\craft\ember\records\ActiveRecord;
use flipbox\patron\Patron;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $instanceId
 * @property string $environment
 */
class ProviderEnvironment extends ActiveRecord
{
    use EnvironmentAttributeTrait,
        InstanceAttributeTrait;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'patron_provider_environments';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = [
        'instanceId'
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->environmentRules(),
            $this->instanceRules(),
            [
                [
                    [
                        'instanceId'
                    ],
                    'required'
                ],
                [
                    [
                        'environment'
                    ],
                    'in',
                    'range' => Patron::getInstance()->getSettings()->getEnvironments()
                ]
            ]
        );
    }
}
