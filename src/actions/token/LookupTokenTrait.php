<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\token;

use flipbox\patron\records\Token;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait LookupTokenTrait
{
    /**
     * @param string $token
     * @return Token
     */
    protected function find($token)
    {
        return Token::findOne([
            'enabled' => null,
            'environment' => null,
            is_numeric($token) ? 'id' : 'accessToken' => $token
        ]);
    }
}
