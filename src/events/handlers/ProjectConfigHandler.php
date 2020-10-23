<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\events\handlers;

use Craft;
use craft\events\ConfigEvent;
use flipbox\patron\events\ManageProviderProjectConfig;
use flipbox\patron\events\ManageTokenProjectConfig;
use flipbox\patron\records\Provider;
use flipbox\patron\records\Token;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.1.0
 */
class ProjectConfigHandler
{
    /**
     * @param ConfigEvent $event
     */
    public static function handleChangedProvider(ConfigEvent $event)
    {
        Event::off(
            Provider::class,
            Provider::EVENT_AFTER_INSERT,
            [
                ManageProviderProjectConfig::class,
                'save'
            ]
        );

        Event::off(
            Provider::class,
            Provider::EVENT_AFTER_UPDATE,
            [
                ManageProviderProjectConfig::class,
                'save'
            ]
        );

        // Get the UID that was matched in the config path
        $uid = $event->tokenMatches[0];

        if (null === ($provider = Provider::findOne([
                'uid' => $uid
            ]))) {
            $provider = new Provider();
        }

        Craft::configure($provider, $event->newValue);

        $provider->save();

        Event::on(
            Provider::class,
            Provider::EVENT_AFTER_INSERT,
            [
                ManageProviderProjectConfig::class,
                'save'
            ]
        );

        Event::on(
            Provider::class,
            Provider::EVENT_AFTER_UPDATE,
            [
                ManageProviderProjectConfig::class,
                'save'
            ]
        );
    }

    /**
     * @param ConfigEvent $event
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function handleDeletedProvider(ConfigEvent $event)
    {
        Event::off(
            Provider::class,
            Provider::EVENT_AFTER_DELETE,
            [
                ManageProviderProjectConfig::class,
                'delete'
            ]
        );

        // Get the UID that was matched in the config path
        $uid = $event->tokenMatches[0];

        if (null === $provider = Provider::findOne([
                'uid' => $uid,
                'enabled' => null
            ])) {
            return;
        }

        $provider->delete();

        Event::on(
            Provider::class,
            Provider::EVENT_AFTER_DELETE,
            [
                ManageProviderProjectConfig::class,
                'delete'
            ]
        );
    }

    /**
     * @param ConfigEvent $event
     */
    public static function handleChangedToken(ConfigEvent $event)
    {
        Event::off(
            Token::class,
            Token::EVENT_AFTER_INSERT,
            [
                ManageTokenProjectConfig::class,
                'save'
            ]
        );

        Event::off(
            Token::class,
            Token::EVENT_AFTER_UPDATE,
            [
                ManageTokenProjectConfig::class,
                'save'
            ]
        );

        // Get the UID that was matched in the config path
        $uid = $event->tokenMatches[0];

        if (null === ($token = Token::findOne([
                'uid' => $uid,
                'enabled' => null
            ]))) {
            $token = new Token();
        }

        // Compare dates from config
        $configDateUpdated = $event->newValue['dateUpdated'] ?? null;
        $tokenDateUpdated = $token->dateUpdated ?? null;

        // If the token has been updated more recently in the database, use it
        if ($configDateUpdated && $tokenDateUpdated && strtotime($tokenDateUpdated) > strtotime($configDateUpdated)) {
            $event->newValue = array_merge(
                $event->newValue,
                [
                    'accessToken' => $token->accessToken,
                ]
            );
        }

        // Ignore
        unset($event->newValue['dateUpdated']);

        Craft::configure($token, $event->newValue);

        $token->save();

        Event::on(
            Token::class,
            Token::EVENT_AFTER_INSERT,
            [
                ManageTokenProjectConfig::class,
                'save'
            ]
        );

        Event::on(
            Token::class,
            Token::EVENT_AFTER_UPDATE,
            [
                ManageTokenProjectConfig::class,
                'save'
            ]
        );
    }

    /**
     * @param ConfigEvent $event
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function handleDeletedToken(ConfigEvent $event)
    {
        Event::off(
            Token::class,
            Token::EVENT_AFTER_DELETE,
            [
                ManageTokenProjectConfig::class,
                'delete'
            ]
        );

        // Get the UID that was matched in the config path
        $uid = $event->tokenMatches[0];

        if (null === $token = Token::findOne([
                'uid' => $uid
            ])) {
            return;
        }

        $token->delete();

        Event::on(
            Token::class,
            Token::EVENT_AFTER_DELETE,
            [
                ManageTokenProjectConfig::class,
                'delete'
            ]
        );
    }

    /**
     * @return array
     */
    public static function rebuild(): array
    {
        $return = [];

        foreach (Provider::findAll(['enabled' => null]) as $provider) {
            $return['plugins.patron.providers'][$provider->uid] = $provider->toProjectConfig();
        }

        foreach (Token::findAll(['enabled' => null]) as $token) {
            $return['plugins.patron.tokens'][$token->uid] = $token->toProjectConfig();
        }

        return $return;
    }
}
