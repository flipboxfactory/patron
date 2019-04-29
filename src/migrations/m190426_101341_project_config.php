<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\migrations;

use craft\db\Migration;
use flipbox\patron\records\Provider;

class m190426_101341_project_config extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            Provider::tableName(),
            'clientId',
            $this->char(Provider::CLIENT_ID_LENGTH)->notNull()
        );

        $this->addColumn(
            Provider::tableName(),
            'clientSecret',
            $this->char(Provider::CLIENT_SECRET_LENGTH)
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}
