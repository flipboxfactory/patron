<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\migrations;

use craft\db\Migration;
use flipbox\patron\records\Provider as ProviderRecord;
use flipbox\patron\records\Token as TokensRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        // Environments
        if (false === (new m180716_121422_environments())->safeUp()) {
            return false;
        }

        // Locks
        if (false === (new m181010_081033_provider_locks())->safeUp()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        // Locks
        if (false === (new m181010_081033_provider_locks())->safeDown()) {
            return false;
        }

        // Environments
        if (false === (new m180716_121422_environments())->safeDown()) {
            return false;
        }

        $this->dropTableIfExists(TokensRecord::tableName());
        $this->dropTableIfExists(ProviderRecord::tableName());

        return true;
    }

    /**
     * Creates the tables.
     *
     * @return void
     */
    protected function createTables()
    {
        $this->createTable(TokensRecord::tableName(), [
            'id' => $this->primaryKey(),
            'accessToken' => $this->text()->notNull(),
            'refreshToken' => $this->text(),
            'providerId' => $this->integer()->notNull(),
            'values' => $this->text(),
            'enabled' => $this->boolean(),
            'dateExpires' => $this->dateTime(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(ProviderRecord::tableName(), [
            'id' => $this->primaryKey(),
            'handle' => $this->string()->notNull(),
            'clientId' => $this->char(ProviderRecord::CLIENT_ID_LENGTH)->notNull(),
            'clientSecret' => $this->char(ProviderRecord::CLIENT_SECRET_LENGTH),
            'scopes' => $this->string(),
            'class' => $this->string()->notNull(),
            'settings' => $this->text(),
            'enabled' => $this->boolean(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);
    }

    /**
     * Creates the indexes.
     *
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(
            $this->db->getIndexName(TokensRecord::tableName(), 'providerId', false, true),
            TokensRecord::tableName(),
            'providerId',
            false
        );
    }

    /**
     * Adds the foreign keys.
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName(TokensRecord::tableName(), 'providerId'),
            TokensRecord::tableName(),
            'providerId',
            ProviderRecord::tableName(),
            'id',
            'CASCADE'
        );
    }
}
