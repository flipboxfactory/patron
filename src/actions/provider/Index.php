<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\provider;

use flipbox\ember\actions\record\RecordIndex;
use flipbox\patron\Patron;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Index extends RecordIndex
{
    /**
     * @inheritdoc
     */
    protected function createQuery(array $config = []): QueryInterface
    {
        return Patron::getInstance()->manageProviders()->getQuery($config);
    }
}
