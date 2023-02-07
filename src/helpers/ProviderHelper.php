<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\helpers;

use craft\helpers\StringHelper;
use ReflectionClass;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ProviderHelper
{
    /**
     * @param $provider
     * @return string
     * @throws \ReflectionException
     */
    public static function displayName($provider): string
    {
        $reflect = new ReflectionClass(
            $provider
        );

        // Split capital letters
        $parts = preg_split("/(?<=[a-z])(?![a-z])/", $reflect->getShortName(), -1, PREG_SPLIT_NO_EMPTY);

        // Assemble
        return StringHelper::toString($parts, ' ');
    }
}
