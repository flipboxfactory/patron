<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\events;

use Craft;
use craft\events\ConfigEvent;
use flipbox\patron\records\Token;
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

        Craft::$app->getProjectConfig()->set(
            'patronTokens.' . $record->uid,
            $record->toProjectConfig()
        );
    }

    /**
     * @param ConfigEvent $event
     */
    public static function delete(ConfigEvent $event)
    {
        /** @var Token $record */
        $record = $event->sender;

        Craft::$app->getProjectConfig()->remove(
            'patronTokens.' . $record->uid
        );
    }
}
