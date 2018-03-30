<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\authorization\traits;

use flipbox\ember\actions\model\traits\Lookup as BaseLookup;
use flipbox\patron\Patron;
use League\OAuth2\Client\Provider\AbstractProvider;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait Lookup
{
    use BaseLookup;

    /**
     * @inheritdoc
     * @param int $id
     * @return AbstractProvider|null
     */
    protected function find(int $id)
    {
        return Patron::getInstance()->getProviders()->find($id);
    }
}
