<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\cp\services;

use Craft;
use flipbox\patron\migrations\AlterEnvironments;
use flipbox\patron\models\Settings as SettingsModel;
use flipbox\patron\Patron;
use yii\base\Component;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Settings extends Component
{
    /**
     * @param SettingsModel $settingsModel
     * @return bool
     * @throws \Throwable
     */
    public function save(SettingsModel $settingsModel)
    {
        // Save plugin settings
        if (Craft::$app->getPlugins()->savePluginSettings(
            Patron::getInstance(),
            $settingsModel->toArray()
        )) {
            // Alter table
            return $this->alterEnvironmentsColumn();
        }

        return false;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    private function alterEnvironmentsColumn(): bool
    {
        $migration = new AlterEnvironments();

        ob_start();
        $migration->up();
        ob_end_clean();

        return true;
    }
}
