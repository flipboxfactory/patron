<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\provider\instance;

use flipbox\ember\actions\record\RecordCreate;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderInstance;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Create extends RecordCreate
{
    use traits\Populate, traits\Save;

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
