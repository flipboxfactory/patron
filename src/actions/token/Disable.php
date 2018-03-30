<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\token;

use flipbox\ember\actions\model\traits\Lookup;
use flipbox\ember\actions\model\traits\Manage;
use flipbox\patron\Patron;
use flipbox\patron\records\Token;
use yii\base\Action;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Disable extends Action
{
    use Manage, Lookup, traits\Lookup {
        run as traitRun;
    }

    /**
     * @param string|int $token
     * @inheritdoc
     */
    public function run($token)
    {
        return $this->traitRun($token);
    }

    /**
     * @inheritdoc
     */
    protected function performAction(Token $token): bool
    {
        return Patron::getInstance()->manageTokens()->disable($token);
    }
}
