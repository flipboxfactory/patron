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
use flipbox\patron\records\ProviderEnvironment;
use flipbox\patron\records\ProviderInstance;
use flipbox\patron\records\Token;
use flipbox\patron\records\TokenEnvironment;

class m181019_220655_provider_settings extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(
            ProviderInstance::tableName(),
            [
                'id' => $this->primaryKey(),
                'providerId' => $this->integer()->notNull(),
                'clientId' => $this->char(ProviderInstance::CLIENT_ID_LENGTH)->notNull(),
                'clientSecret' => $this->char(ProviderInstance::CLIENT_SECRET_LENGTH),
                'settings' => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                ProviderInstance::tableName(),
                'providerId'
            ),
            ProviderInstance::tableName(),
            'providerId',
            Provider::tableName(),
            'id',
            'CASCADE'
        );

        $this->addColumn(
            ProviderEnvironment::tableName(),
            'settingsId',
            $this->integer()
        );

        // Todo - migrate existing enviornments

        // Make the provider handle unique
        $this->createIndex(
            $this->db->getIndexName(
                Provider::tableName(),
                'handle',
                true,
                false
            ),
            Provider::tableName(),
            'handle',
            true
        );

        // Uncomment after migration

//        $this->dropPrimaryKey(
//            null,
//            ProviderEnvironment::tableName(),
//            [
//                'providerId',
//                'environment'
//            ]
//        );
//
//        $this->addPrimaryKey(
//            null,
//            ProviderEnvironment::tableName(),
//            [
//                'settingsId',
//                'environment'
//            ]
//        );
//
//        $this->dropColumn(
//            Provider::tableName(),
//            'settings'
//        );
//
//        $this->dropColumn(
//            Provider::tableName(),
//            'clientId'
//        );
//
//        $this->dropColumn(
//            Provider::tableName(),
//            'clientSecret'
//        );
//
//        $this->alterColumn(
//            ProviderEnvironment::tableName(),
//            'settingsId',
//            $this->integer()->notNull()
//        );
//
//        // Apply foreign key to settings
//        $this->addForeignKey(
//            $this->db->getForeignKeyName(
//                ProviderEnvironment::tableName(),
//                'settingsId'
//            ),
//            ProviderEnvironment::tableName(),
//            'settingsId',
//            ProviderSettings::tableName(),
//            'id',
//            'CASCADE'
//        );
//
//        // Remove existing foreign key
//        $this->dropForeignKey(
//            $this->db->getForeignKeyName(
//                ProviderEnvironment::tableName(),
//                'providerId'
//            ),
//            ProviderEnvironment::tableName()
//        );
//
//        $this->dropColumn(
//            ProviderEnvironment::tableName(),
//            'providerId'
//        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(ProviderInstance::tableName());

        return true;
    }
}
