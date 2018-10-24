<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\migrations;

use craft\db\Migration;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderInstance;

class m181018_081114_encrypt extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn(
            Provider::tableName(),
            'clientSecret',
            $this->char(ProviderInstance::CLIENT_SECRET_LENGTH)
        );

        // Encrypt those that are not
        if (Patron::getInstance()->getSettings()->getEncryptStorageData() === true) {
            Patron::getInstance()->manageProviders()->changeEncryption(true);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}
