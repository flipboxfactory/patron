<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\provider;

use flipbox\ember\actions\model\traits\Lookup;
use flipbox\ember\actions\model\traits\Manage;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
use yii\base\Action;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Enable extends Action
{
    use Manage, Lookup, traits\Lookup {
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
     */
    protected function performAction(Provider $record): bool
    {
        return Patron::getInstance()->manageProviders()->enable($record);
    }
}
