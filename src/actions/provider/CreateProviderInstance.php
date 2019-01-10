<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\provider;

use flipbox\craft\ember\actions\records\CreateRecord;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderInstance;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class CreateProviderInstance extends CreateRecord
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
     * @return Provider
     */
    protected function newRecord(array $config = []): ActiveRecord
    {
        /** @var ProviderInstance $record */
        $record = new ProviderInstance();

        // Do we need to set properties too
        if (!empty($config)) {
            $record->setAttributes($config);
        }

        return $record;
    }
}
