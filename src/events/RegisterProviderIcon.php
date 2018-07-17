<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\events;

use flipbox\patron\providers\Base;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class RegisterProviderIcon extends Event
{
    /**
     * The event name
     */
    const REGISTER_ICON = 'registerProviderIcon';

    /**
     * @var string
     */
    public $icon;
}
