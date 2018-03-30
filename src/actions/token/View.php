<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\token;

use flipbox\ember\actions\record\RecordView;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class View extends RecordView
{
    use traits\Lookup;

    /**
     * @inheritdoc
     */
    public function run($token)
    {
        return parent::run($token);
    }
}
