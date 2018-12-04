<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\provider;

use flipbox\craft\ember\actions\records\UpdateRecord;
use flipbox\patron\records\ProviderInstance;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UpdateProviderInstance extends UpdateRecord
{
    /**
     * @inheritdoc
     */
    public $validBodyParams = [
        'provider',
        'clientId',
        'clientSecret',
        'settings',
        'environments'
    ];

    /**
     * @inheritdoc
     */
    public function run($instance)
    {
        return parent::run($instance);
    }

    /**
     * @inheritdoc
     * @return ProviderInstance|null
     */
    protected function find($id)
    {
        return ProviderInstance::findOne($id);
    }
}
