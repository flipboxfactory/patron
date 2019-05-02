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
class RegisterProviderInfo extends Event
{
    /**
     * The event name
     */
    const REGISTER_INFO = 'registerProviderInfo';

    /**
     * The base icon path
     */
    const ICON_PATH = '@vendor/flipboxfactory/patron/src/icons/';

    /**
     * An array of additional provider information.
     *
     * ```
     * [
     *      Provider::class => [
     *          'name' => 'Provider Name',
     *          'icon' => 'path/to/icon.svg'
     *      ]
     * ]
     * ```
     *
     * @var array
     */
    public $info = [
        Google::class => [
            'name' => 'Google',
            'icon' => self::ICON_PATH . 'google.svg'
        ],
        LinkedIn::class => [
            'name' => 'LinkedIn',
            'icon' => self::ICON_PATH . 'linkedin.svg'
        ],
        Facebook::class => [
            'name' => 'Facebook',
            'icon' => self::ICON_PATH . 'facebook.svg'
        ],
        Instagram::class => [
            'name' => 'Instagram',
            'icon' => self::ICON_PATH . 'instagram.svg'
        ],
        Github::class => [
            'name' => 'GitHub',
            'icon' => self::ICON_PATH . 'github.svg'
        ]
    ];
}
