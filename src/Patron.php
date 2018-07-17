<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use flipbox\patron\models\Settings as SettingsModel;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method SettingsModel getSettings()
 *
 * @property services\Providers $providers
 * @property services\Tokens $tokens
 * @property services\ManageProviders $manageProviders
 * @property services\ManageTokens $manageTokens
 * @property services\Session $session
 */
class Patron extends Plugin
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Components
        $this->setComponents([
            'providers' => services\Providers::class,
            'tokens' => services\Tokens::class,
            'manageProviders' => services\ManageProviders::class,
            'manageTokens' => services\ManageTokens::class,
            'session' => services\Session::class
        ]);

        // Modules
        $this->setModules([
            'cp' => cp\Cp::class

        ]);

        // Template variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('patron', self::getInstance());
            }
        );

        // CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            [self::class, 'onRegisterCpUrlRules']
        );

        // Register our site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                if ($callbackUrlRule = $this->getSettings()->getCallbackUrlRule()) {
                    $event->rules = array_merge(
                        $event->rules,
                        $callbackUrlRule
                    );
                }
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem()
    {
        return array_merge(
            parent::getCpNavItem(),
            [
                'subnav' => [
                    'patron.providers' => [
                        'label' => Craft::t('patron', 'Providers'),
                        'url' => 'patron/providers',
                    ],
                    'patron.settings' => [
                        'label' => Craft::t('patron', 'Settings'),
                        'url' => 'patron/settings',
                    ]
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     * @return SettingsModel
     */
    public function createSettingsModel()
    {
        return new SettingsModel();
    }

    /**
     * @inheritdoc
     * @throws \yii\base\ExitException
     */
    public function getSettingsResponse()
    {
        Craft::$app->getResponse()->redirect(
            UrlHelper::cpUrl('patron/settings')
        );

        Craft::$app->end();
    }

    /*******************************************
     * SERVICES
     *******************************************/

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return services\Providers
     */
    public function getProviders(): services\Providers
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('providers');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return services\Tokens
     */
    public function getTokens(): services\Tokens
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('tokens');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return services\ManageProviders
     */
    public function manageProviders(): services\ManageProviders
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('manageProviders');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return services\ManageTokens
     */
    public function manageTokens(): services\ManageTokens
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('manageTokens');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return services\Session
     */
    public function getSession(): services\Session
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('session');
    }


    /*******************************************
     * MODULES
     *******************************************/

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return cp\Cp
     */
    public function getCp(): cp\Cp
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getModule('cp');
    }

    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @param RegisterUrlRulesEvent $event
     */
    public static function onRegisterCpUrlRules(RegisterUrlRulesEvent $event)
    {
        $event->rules = array_merge(
            $event->rules,
            [
                // SETTINGS
                'patron/settings' => 'patron/cp/view/settings/index',

                'patron' => 'patron/cp/view/general/index',
                'patron/providers' => 'patron/cp/view/providers/index',
                'patron/providers/new' => 'patron/cp/view/providers/upsert',
                'patron/providers/<identifier:\d+>' => 'patron/cp/view/providers/upsert',
                'patron/providers/<identifier:\d+>/tokens' => 'patron/cp/view/providers/tokens',
            ]
        );
    }


    /*******************************************
     * LOGGING
     *******************************************/

    /**
     * Logs an informative message.
     *
     * @param $message
     * @param string $category
     */
    public static function info($message, $category = 'patron')
    {
        Craft::info($message, $category);
    }

    /**
     * Logs a warning message.
     *
     * @param $message
     * @param string $category
     */
    public static function warning($message, $category = 'patron')
    {
        Craft::warning($message, $category);
    }

    /**
     * Logs an error message.
     *
     * @param $message
     * @param string $category
     */
    public static function error($message, $category = 'patron')
    {
        Craft::error($message, $category);
    }
}
