<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\events\handlers;

use flipbox\patron\Patron;
use flipbox\patron\records\ProviderInstance;
use yii\base\ModelEvent;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class BeforeInsertProviderInstance
{
    /**
     * @param ModelEvent $event
     */
    public static function handle(ModelEvent $event)
    {
        $defaultEnvironments = Patron::getInstance()->getSettings()->getDefaultEnvironments();

        /** @var ProviderInstance $provider */
        $provider = $event->sender;

        // Ignore if already set
        if ($provider->isRelationPopulated('environments') === true) {
            return;
        }

        $provider->setEnvironments($defaultEnvironments);
        $provider->autoSaveEnvironments = true;
    }
}
