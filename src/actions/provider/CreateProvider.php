<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\provider;

use flipbox\craft\ember\actions\records\CreateRecord;
use flipbox\patron\records\Provider;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class CreateProvider extends CreateRecord
{
    use PopulateSettingsTrait;

    /**
     * @inheritdoc
     */
    public $validBodyParams = [
        'handle',
        'clientId',
        'clientSecret',
        'scopes',
        'class',
        'enabled'
    ];

    /**
     * @param ActiveRecord|Provider $record
     * @return ActiveRecord
     */
    protected function populate(ActiveRecord $record): ActiveRecord
    {
        parent::populate($record);
        $this->populateSettings($record);

        return $record;
    }

    /**
     * @inheritdoc
     * @return Provider
     */
    protected function newRecord(array $config = []): ActiveRecord
    {
        /** @var Provider $record */
        $record = new Provider();

        // Do we need to set properties too
        if (!empty($config)) {
            $record->setAttributes($config);
        }

        return $record;
    }
}
