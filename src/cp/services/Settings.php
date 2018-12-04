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
use flipbox\patron\records\Provider;
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
        $currentSettings = Patron::getInstance()->getSettings();

        $encryptionChanged = $currentSettings->getEncryptStorageData() != $settingsModel->getEncryptStorageData();

        // Save plugin settings
        if (Craft::$app->getPlugins()->savePluginSettings(
            Patron::getInstance(),
            $settingsModel->toArray()
        )) {
            // Change encryption
            if ($encryptionChanged) {
                $this->changeEncryption(
                    $settingsModel->getEncryptStorageData()
                );
            }

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

    /*******************************************
     * ENCRYPTION
     *******************************************/

    /**
     * @param bool $changeTo
     * @return void
     */
    public function changeEncryption(bool $changeTo)
    {
        // Temp
        Patron::getInstance()->getSettings()->setEncryptStorageData(!$changeTo);

        // Get current providers
        $records = Provider::findAll([
            'enabled' => null,
            'environment' => null
        ]);

        // Temp
        Patron::getInstance()->getSettings()->setEncryptStorageData($changeTo);

        // Iterate and save
        foreach ($records as $record) {
            Patron::info(
                'Altering Provider::$clientSecret encryption preferences'
            );

            $record->save();
        }
    }
}
