<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\provider;

use flipbox\craft\ember\actions\records\UpdateRecord;
use flipbox\patron\records\Provider;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UpdateProvider extends UpdateRecord
{
    /**
     * @inheritdoc
     */
    public $validBodyParams = [
        'handle',
        'scopes',
        'class',
        'enabled'
    ];

    /**
     * @inheritdoc
     */
    public function run($provider)
    {
        return parent::run($provider);
    }

    /**
     * @inheritdoc
     * @return Provider|null
     */
    protected function find($id)
    {
        return Provider::findOne([
            'enabled' => null,
            'environment' => null,
            'id' => $id
        ]);
    }
}
