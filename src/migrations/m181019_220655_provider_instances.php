<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\MigrationHelper;
use flipbox\patron\events\handlers\BeforeInsertProviderInstance;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderEnvironment;
use flipbox\patron\records\ProviderInstance;
use yii\base\Event;

class m181019_220655_provider_instances extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // No insert events please...
        Event::off(
            ProviderInstance::class,
            ProviderInstance::EVENT_BEFORE_INSERT
        );

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
            'instanceId',
            $this->integer()
        );

        // Migrates all the existing providers to new 'instance' types
        $this->migrateToInstances();

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

        $this->dropColumn(
            Provider::tableName(),
            'settings'
        );

        $this->dropColumn(
            Provider::tableName(),
            'clientId'
        );

        $this->dropColumn(
            Provider::tableName(),
            'clientSecret'
        );

        // We should be able to enforce notNull now
        $this->alterColumn(
            ProviderEnvironment::tableName(),
            'instanceId',
            $this->integer()->notNull()
        );

        // Apply foreign key to settings
        $this->addForeignKey(
            $this->db->getForeignKeyName(
                ProviderEnvironment::tableName(),
                'instanceId'
            ),
            ProviderEnvironment::tableName(),
            'instanceId',
            ProviderInstance::tableName(),
            'id',
            'CASCADE'
        );

        // Remove existing foreign key
        $this->dropForeignKey(
            $this->db->getForeignKeyName(
                ProviderEnvironment::tableName(),
                'providerId'
            ),
            ProviderEnvironment::tableName()
        );

        $this->dropColumn(
            ProviderEnvironment::tableName(),
            'providerId'
        );

        $this->dropPrimaryKey(
            $this->db->getPrimaryKeyName(
                ProviderEnvironment::tableName(),
                [
                    'environment'
                ]
            ),
            ProviderEnvironment::tableName()
        );

        $this->addPrimaryKey(
            $this->db->getPrimaryKeyName(
                ProviderEnvironment::tableName(),
                [
                    'instanceId',
                    'environment'
                ]
            ),
            ProviderEnvironment::tableName(),
            [
                'instanceId',
                'environment'
            ]
        );
    }

    /**
     *
     */
    protected function migrateToInstances()
    {
        $query = (new Query)
            ->select(['id', 'settings', 'clientId', 'clientSecret'])
            ->from(Provider::tableName());

        $rows = $query->all();

        foreach ($rows as $row) {
            $instance = new ProviderInstance();
            $instance->setProviderId($row['id']);
            $instance->settings = $row['settings'];
            $instance->clientId = $row['clientId'];
            $instance->clientSecret = $row['clientSecret'];

            if (!$instance->save()) {
                continue;
            }

            /** @var ProviderEnvironment[] $environments */
            $environments = ProviderEnvironment::findAll([
                'providerId' => $row['id']
            ]);

            foreach ($environments as $environment) {
                $environment->instanceId = $instance->getId();
                $environment->save(true, ['instanceId']);
            }
        }
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
