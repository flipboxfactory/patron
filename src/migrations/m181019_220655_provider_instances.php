<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\migrations;

use craft\db\Migration;

/**
 * Class m181019_220655_provider_instances
 * @package flipbox\patron\migrations
 *
 * @deprecated
 */
class m181019_220655_provider_instances extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        return true;
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
