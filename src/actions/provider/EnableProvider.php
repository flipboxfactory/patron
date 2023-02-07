<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\provider;

use flipbox\craft\ember\actions\records\LookupRecordTrait;
use flipbox\craft\ember\actions\records\ManageRecordTrait;
use flipbox\patron\records\Provider;
use yii\base\Action;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class EnableProvider extends Action
{
    use ManageRecordTrait, LookupRecordTrait {
        run as traitRun;
    }

    /**
     * @inheritdoc
     */
    public function run($provider)
    {
        return $this->traitRun($provider);
    }

    /**
     * @inheritdoc
     * @return Provider|null
     */
    protected function find($id)
    {
        return Provider::findOne([
            'enabled' => null,
            'id' => $id
        ]);
    }

    /**
     * @param Provider $record
     * @inheritdoc
     */
    protected function performAction(ActiveRecord $record): bool
    {
        if (!$record instanceof Provider) {
            return false;
        }

        $record->enabled = true;
        return $record->save(true, ['enabled']);
    }
}
