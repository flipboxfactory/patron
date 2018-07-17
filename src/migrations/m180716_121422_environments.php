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
use flipbox\patron\records\Token;
use flipbox\patron\records\TokenEnvironment;

class m180716_121422_environments extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(
            ProviderEnvironment::tableName(),
            [
                'providerId' => $this->integer()->notNull(),
                'environment' => $this->enum(
                    'environment',
                    Patron::getInstance()->getSettings()->getEnvironments()
                )->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]
        );

        $this->createTable(
            TokenEnvironment::tableName(),
            [
                'tokenId' => $this->integer()->notNull(),
                'environment' => $this->enum(
                    'environment',
                    Patron::getInstance()->getSettings()->getEnvironments()
                )->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]
        );

        $this->addPrimaryKey(
            null,
            ProviderEnvironment::tableName(),
            [
                'providerId',
                'environment'
            ]
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                ProviderEnvironment::tableName(),
                'providerId'
            ),
            ProviderEnvironment::tableName(),
            'providerId',
            Provider::tableName(),
            'id',
            'CASCADE'
        );

        $this->addPrimaryKey(
            null,
            TokenEnvironment::tableName(),
            [
                'tokenId',
                'environment'
            ]
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                TokenEnvironment::tableName(),
                'tokenId'
            ),
            TokenEnvironment::tableName(),
            'tokenId',
            Token::tableName(),
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180716_121422_environments cannot be reverted.\n";
        return false;
    }
}
