<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\events;

use Craft;
use flipbox\patron\Patron;
use flipbox\patron\records\Token;
use yii\base\Event;
use yii\db\AfterSaveEvent;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.1.0
 */
class ManageTokenProjectConfig
{
    /**
     * @param AfterSaveEvent $event
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     * @throws \yii\base\NotSupportedException
     * @throws \yii\web\ServerErrorHttpException
     */
    public static function save(AfterSaveEvent $event)
    {
        /** @var Token $record */
        $record = $event->sender;

        if (static::bypassSavingToProjectConfig($event->changedAttributes)) {
            Patron::warning(
                "Saving Token to project config is not possible while in read-only mode.",
                __METHOD__
            );
            return;
        }

        Craft::$app->getProjectConfig()->set(
            'plugins.patron.tokens.' . $record->uid,
            $record->toProjectConfig()
        );
    }

    /**
     * @param Event $event
     */
    public static function delete(Event $event)
    {
        /** @var Token $record */
        $record = $event->sender;

        Craft::$app->getProjectConfig()->remove(
            'plugins.patron.tokens.' . $record->uid
        );
    }

    /**
     * In some automated scenarios, we want to bypass saving to the project config.
     *
     * @param array $changedAttributes
     * @return bool
     */
    protected static function bypassSavingToProjectConfig(array $changedAttributes): bool
    {
        // If project config is not in read only mode, don't bypass
        if (!Craft::$app->getProjectConfig()->readOnly) {
            return false;
        }

        // Also, only allow the 'accessToken' to be updated -> this would be triggered by a refresh token operation
        return count($changedAttributes) === 2 && array_key_exists('accessToken', $changedAttributes);
    }
}
