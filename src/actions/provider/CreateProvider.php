<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\provider;

use flipbox\craft\ember\actions\records\CreateRecord;
use flipbox\patron\records\Provider;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class CreateProvider extends CreateRecord
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
     * @return Provider
     */
    protected function newRecord(array $config = []): ActiveRecord
    {
        /** @var Provider $record */
        $record = new Provider();

        // Do we need to set properties too
        if (!empty($config)) {
            $record->setAttributes($config);
        }

        return $record;
    }
}
