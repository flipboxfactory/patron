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
     */
    public function safeUp()
    {
        $type = $this->enum(
            self::COLUMN_NAME,
            Patron::getInstance()->getSettings()->getEnvironments()
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
     * @inheritdoc
     */
    public function safeDown()
    {
        return false;
    }
}
