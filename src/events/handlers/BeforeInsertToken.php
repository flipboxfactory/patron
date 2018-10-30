<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\events\handlers;

use flipbox\patron\Patron;
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
        
        $token->setEnvironments((array_unique(self::getEnvironments($token))));
        $token->autoSaveEnvironments = true;
    }

    /**
     * @param Token $token
     * @return array
     */
    protected static function getEnvironments(Token $token): array
    {
        $settings = Patron::getInstance()->getSettings();

        if ($settings->getApplyProviderEnvironmentsToToken() === true) {
            return self::getEnvironmentsFromTokenProvider($token);
        }

        return [$settings->getEnvironment()];
    }

    /**
     * @param Token $token
     * @return array
     */
    protected static function getEnvironmentsFromTokenProvider(Token $token): array
    {
        $environments = [];

        foreach ($token->instances as $instance) {
            $environments = array_merge(
                $environments,
                $instance->getEnvironments()
                    ->select('environment')
                    ->indexBy(null)
                    ->column()
            );
        }

        return $environments;
    }
}
