<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\events;

use flipbox\patron\records\Token;
use League\OAuth2\Client\Token\AccessToken;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class PersistTokenEvent extends Event
{
    /**
     * @var AccessToken
     */
    public $token;

    /**
     * @var Token
     */
    public $record;
}