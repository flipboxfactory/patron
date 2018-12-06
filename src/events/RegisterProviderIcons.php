<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\events;

use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Instagram;
use League\OAuth2\Client\Provider\LinkedIn;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class RegisterProviderIcons extends Event
{
    /**
     * The event name
     */
    const REGISTER_ICON = 'registerProviderIcon';

    /**
     * The base icon path
     */
    const ICON_PATH = '@vendor/flipboxfactory/patron/src/icons/';

    /**
     * @var string
     */
    public $icons = [
        Google::class => self::ICON_PATH . 'google.svg',
        LinkedIn::class => self::ICON_PATH . 'linkedin.svg',
        Facebook::class => self::ICON_PATH . 'facebook.svg',
        Instagram::class => self::ICON_PATH . 'instagram.svg',
        Github::class => self::ICON_PATH . 'github.svg',
    ];
}
