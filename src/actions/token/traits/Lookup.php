<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\token\traits;

use flipbox\patron\Patron;
use flipbox\patron\records\Token;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait Lookup
{
    /**
     * @param string $accessCode
     * @return Token
     */
    protected function find($accessCode)
    {
        return Patron::getInstance()->manageTokens()->find($accessCode);
    }
}
