<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\migrations;

use craft\db\Migration;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderLock;

class m181010_081033_provider_locks extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(
            ProviderLock::tableName(),
            [
                'providerId' => $this->integer()->notNull(),
                'pluginId' => $this->integer()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]
        );

        $this->addPrimaryKey(
            null,
            ProviderLock::tableName(),
            [
                'providerId',
                'pluginId'
            ]
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                ProviderLock::tableName(),
                'providerId'
            ),
            ProviderLock::tableName(),
            'providerId',
            Provider::tableName(),
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                ProviderLock::tableName(),
                'pluginId'
            ),
            ProviderLock::tableName(),
            'pluginId',
            '{{%plugins}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(ProviderLock::tableName());

        return true;
    }
}
