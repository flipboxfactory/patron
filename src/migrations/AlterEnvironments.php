<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\patron\migrations;

use craft\db\Migration;
use flipbox\patron\Patron;
use flipbox\patron\records\ProviderEnvironment;
use flipbox\patron\records\TokenEnvironment;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class AlterEnvironments extends Migration
{
    /**
     * The state column name
     */
    const COLUMN_NAME = 'environment';

    /**
     * @inheritdoc
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function safeUp()
    {
        $environments = Patron::getInstance()->getSettings()->getEnvironments();

        $this->deleteOldEnvironments($environments);

        $type = $this->enum(
            self::COLUMN_NAME,
            $environments
        )->notNull();

        $this->alterColumn(
            ProviderEnvironment::tableName(),
            self::COLUMN_NAME,
            $type
        );

        $this->alterColumn(
            TokenEnvironment::tableName(),
            self::COLUMN_NAME,
            $type
        );
    }

    /**
     * @param array $environments
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    protected function deleteOldEnvironments(array $environments)
    {
        $this->deleteOldProviderEnvironments($environments);
        $this->deleteOldTokenEnvironments($environments);
    }

    /**
     * @param array $environments
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function deleteOldTokenEnvironments(array $environments)
    {
        $records = TokenEnvironment::find()
            ->andWhere([
                'NOT IN',
                self::COLUMN_NAME,
                $environments
            ])
            ->all();

        foreach ($records as $record) {
            $record->delete();
        }
    }

    /**
     * @param array $environments
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function deleteOldProviderEnvironments(array $environments)
    {
        $records = ProviderEnvironment::find()
            ->andWhere([
                'NOT IN',
                self::COLUMN_NAME,
                $environments
            ])
            ->all();

        foreach ($records as $record) {
            $record->delete();
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return false;
    }
}
