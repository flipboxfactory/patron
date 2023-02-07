<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\token;

use flipbox\craft\ember\actions\LookupTrait;
use flipbox\craft\ember\actions\ManageTrait;
use flipbox\patron\records\Token;
use yii\base\Action;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class DisableToken extends Action
{
    use ManageTrait, LookupTrait, LookupTokenTrait {
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
     * @param Token $token
     * @inheritdoc
     */
    protected function performAction($token): bool
    {
        if (!$token instanceof Token) {
            return false;
        }

        $token->enabled = false;
        return $token->save(true, ['enabled']);
    }
}
