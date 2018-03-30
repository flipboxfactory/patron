<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\provider;

use flipbox\ember\actions\record\RecordDelete;
use flipbox\patron\records\Provider;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Delete extends RecordDelete
{
    use traits\Lookup;

    /**
     * @inheritdoc
     */
    public function run($provider)
    {
        return parent::run($provider);
    }

    /**
     * @inheritdoc
     * @param Provider $record
     */
    protected function performAction(ActiveRecord $record): bool
    {
        return $record->delete();
    }
}
