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
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\craft\ember\modules\LoggerTrait;
use flipbox\patron\models\Settings as SettingsModel;
use flipbox\patron\queries\ProviderQuery;
use flipbox\patron\queries\TokenQuery;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method SettingsModel getSettings()
 *
 * @property services\Session $session
 */
class Patron extends Plugin
{
    use LoggerTrait;

    /**
     * The before persist token event name
     */
    const EVENT_BEFORE_PERSIST_TOKEN = 'beforePersistToken';

    /**
     *  The after persist token event name
     */
    const EVENT_AFTER_PERSIST_TOKEN = 'afterPersistToken';

    /**
     * @return string
     */
    protected static function getLogFileName(): string
    {
        return 'patron';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Components
        $this->setComponents([
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

        // Add default environments upon creation
        $defaultEnvironments = $this->getSettings()->getDefaultEnvironments();
        if (!empty($defaultEnvironments)) {
            Event::on(
                records\ProviderInstance::class,
                records\ProviderInstance::EVENT_BEFORE_INSERT,
                [
                    events\handlers\BeforeInsertProviderInstance::class,
                    'handle'
                ]
            );
        }

        // Replicate environments to token
        if ($this->getSettings()->getAutoPopulateTokenEnvironments() === true) {
            Event::on(
                records\Token::class,
                records\Token::EVENT_BEFORE_INSERT,
                [
                    events\handlers\BeforeInsertToken::class,
                    'handle'
                ]
            );
        }
    }


    /*******************************************
     * NAV
     *******************************************/

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


    /*******************************************
     * SETTINGS
     *******************************************/

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
     * QUERIES
     *******************************************/

    /**
     * @param array $config
     * @return ProviderQuery
     */
    public function getProviders(array $config = []): ProviderQuery
    {
        $query = new ProviderQuery();

        QueryHelper::configure(
            $query,
            $config
        );

        return $query;
    }

    /**
     * @param array $config
     * @return TokenQuery
     */
    public function getTokens(array $config = []): TokenQuery
    {
        $query = new TokenQuery();

        QueryHelper::configure(
            $query,
            $config
        );

        return $query;
    }


    /*******************************************
     * SERVICES
     *******************************************/

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

                // BASE
                'patron' =>
                    'patron/cp/view/general/index',

                // PROVIDERS
                'patron/providers' =>
                    'patron/cp/view/providers/default/index',

                'patron/providers/new' =>
                    'patron/cp/view/providers/default/upsert',

                // INSTANCES
                'patron/providers/<identifier:\d+>' =>
                    'patron/cp/view/providers/default/upsert',

                'patron/providers/<provider:\d+>/instances/<identifier:\d+>' =>
                    'patron/cp/view/providers/instances/upsert',

                'patron/providers/<provider:\d+>/instances/new' =>
                    'patron/cp/view/providers/instances/upsert',

                // TOKENS
                'patron/providers/<provider:\d+>/tokens' =>
                    'patron/cp/view/providers/tokens/index',

                'patron/providers/<provider:\d+>/tokens/<identifier:\d+>' =>
                    'patron/cp/view/providers/tokens/upsert',
            ]
        );
    }
}
