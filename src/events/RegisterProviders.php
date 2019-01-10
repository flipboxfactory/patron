<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\events;

use League\OAuth2\Client\Provider\AbstractProvider;
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
class RegisterProviders extends Event
{
    /**
     * Event to register providers
     */
    const REGISTER_PROVIDERS = 'registerProviders';

    /**
     * @var AbstractProvider[]
     */
    public $providers = [
        LinkedIn::class,
        Facebook::class,
        Instagram::class,
        Google::class,
        Github::class
    ];
}
