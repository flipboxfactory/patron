<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\events\handlers;

use flipbox\patron\records\Token;
use yii\base\ModelEvent;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class BeforeInsertToken
{
    /**
     * Assign default environments to a provider
     * @param ModelEvent $event
     */
    public static function handle(ModelEvent $event)
    {
        /** @var Token $token */
        $token = $event->sender;

        // Ignore if already set
        if ($token->isRelationPopulated('environments') === true) {
            return;
        }

        $token->setEnvironments(
            $token->getProvider()->getEnvironments()->select('environment')->column()
        );
        $token->autoSaveEnvironments = true;
    }
}
