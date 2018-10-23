<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\provider\instance\traits;

use flipbox\patron\records\Provider;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait Save
{
    /**
     * @inheritdoc
     * @param Provider $record
     */
    protected function performAction(ActiveRecord $record): bool
    {
        return $record->save();
    }
}
