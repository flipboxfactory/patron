<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\settings;

use flipbox\patron\Patron;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property Patron $module
 */
interface SettingsInterface
{
    /**
     * @return string
     */
    public function inputHtml(): string;

    /**
     * @param null $attributeNames
     * @param bool $clearErrors
     * @return mixed
     */
    public function validate($attributeNames = null, $clearErrors = true);

    /**
     * A string representation of the settings, for database storage
     *
     * @return mixed
     */
    public function __toString();
}
