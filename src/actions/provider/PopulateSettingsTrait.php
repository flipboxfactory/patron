<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\provider;

use Craft;
use flipbox\patron\records\Provider;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.1.0
 */
trait PopulateSettingsTrait
{
    /**
     * @param Provider $record
     * @return Provider
     */
    protected function populateSettings(Provider $record): Provider
    {
        $allSettings = Craft::$app->getRequest()->getBodyParam('settings');

        if (!is_array($allSettings)) {
            $allSettings = [$allSettings];
        }

        $record->settings = $allSettings[$record->class] ?? null;;

        return $record;
    }
}
