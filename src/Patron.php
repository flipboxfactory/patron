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
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use flipbox\patron\models\Settings as SettingsModel;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method SettingsModel getSettings()
 */
class Patron extends Plugin
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

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
     * @return SettingsModel
     */
    public function createSettingsModel()
    {
        return new SettingsModel();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'patron/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }

    /*******************************************
     * SERVICES
     *******************************************/
    /**
     * @return services\Providers
     */
    public function getProviders()
    {
        return $this->get('providers');
    }

    /**
     * @return services\Tokens
     */
    public function getTokens()
    {
        return $this->get('tokens');
    }

    /**
     * @return services\ManageProviders
     */
    public function manageProviders()
    {
        return $this->get('manageProviders');
    }

    /**
     * @return services\ManageTokens
     */
    public function manageTokens()
    {
        return $this->get('manageTokens');
    }

    /**
     * @return services\Session
     */
    public function getSession()
    {
        return $this->get('session');
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
