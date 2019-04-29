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
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\services\ProjectConfig;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use flipbox\craft\ember\helpers\ObjectHelper;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\craft\ember\modules\LoggerTrait;
use flipbox\patron\events\handlers\ProjectConfigHandler;
use flipbox\patron\events\RegisterProviderSettings;
use flipbox\patron\models\Settings as SettingsModel;
use flipbox\patron\queries\ProviderQuery;
use flipbox\patron\queries\TokenQuery;
use flipbox\patron\records\Provider;
use flipbox\patron\records\Token;
use flipbox\patron\settings\BaseSettings;
use flipbox\patron\settings\SettingsInterface;
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


        // Project config
        $this->registerProjectConfigEvents();


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
     * Register project config events, if we're able to
     */
    protected function registerProjectConfigEvents()
    {
        if (!version_compare(Craft::$app->getVersion(), '3.1', '>=')) {
            return;
        }

        // Project Config
        Craft::$app->projectConfig
            ->onAdd(
                'patronProviders.{uid}',
                [
                    ProjectConfigHandler::class,
                    'handleChangedProvider'
                ]
            )
            ->onUpdate(
                'patronProviders.{uid}',
                [ProjectConfigHandler::class,
                    'handleChangedProvider'
                ]
            )
            ->onRemove(
                'patronProviders.{uid}',
                [ProjectConfigHandler::class,
                    'handleDeletedProvider'
                ]
            )
            ->onAdd(
                'patronTokens.{uid}',
                [ProjectConfigHandler::class,
                    'handleChangedToken'
                ]
            )
            ->onUpdate(
                'patronTokens.{uid}',
                [ProjectConfigHandler::class,
                    'handleChangedToken'
                ]
            )
            ->onRemove(
                'patronTokens.{uid}',
                [ProjectConfigHandler::class,
                    'handleDeletedToken'
                ]
            );

        Event::on(
            ProjectConfig::class,
            ProjectConfig::EVENT_REBUILD,
            [
                events\handlers\ProjectConfigHandler::class,
                'rebuild'
            ]
        );

        Event::on(
            Provider::class,
            Provider::EVENT_AFTER_INSERT,
            [
                events\ManageProviderProjectConfig::class,
                'save'
            ]
        );

        Event::on(
            Provider::class,
            Provider::EVENT_AFTER_UPDATE,
            [
                events\ManageProviderProjectConfig::class,
                'save'
            ]
        );

        Event::on(
            Provider::class,
            Provider::EVENT_AFTER_DELETE,
            [
                events\ManageProviderProjectConfig::class,
                'delete'
            ]
        );

        Event::on(
            Token::class,
            Token::EVENT_AFTER_INSERT,
            [
                events\ManageTokenProjectConfig::class,
                'save'
            ]
        );

        Event::on(
            Token::class,
            Token::EVENT_AFTER_UPDATE,
            [
                events\ManageTokenProjectConfig::class,
                'save'
            ]
        );

        Event::on(
            Token::class,
            Token::EVENT_AFTER_DELETE,
            [
                events\ManageTokenProjectConfig::class,
                'delete'
            ]
        );
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
     * PROVIDER SETTINGS
     *******************************************/

    /**
     * @param string|null $class
     * @param array $settings
     * @return SettingsInterface
     * @throws \yii\base\InvalidConfigException
     */
    public function providerSettings(string $class = null, $settings = []): SettingsInterface
    {
        if (null === $class) {
            return new BaseSettings();
        }

        $event = new RegisterProviderSettings();

        RegisterProviderSettings::trigger(
            $class,
            RegisterProviderSettings::REGISTER_SETTINGS,
            $event
        );

        if (is_string($settings)) {
            $settings = Json::decodeIfJson($settings);
        }

        if (!is_array($settings)) {
            $settings = ArrayHelper::toArray($settings, []);
        }

        $settings['class'] = $event->class;

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ObjectHelper::create($settings, SettingsInterface::class);
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

                'patron/providers/<identifier:\d+>' =>
                    'patron/cp/view/providers/default/upsert',

                // TOKENS
                'patron/providers/<provider:\d+>/tokens' =>
                    'patron/cp/view/providers/tokens/index',

                'patron/providers/<provider:\d+>/tokens/<identifier:\d+>' =>
                    'patron/cp/view/providers/tokens/upsert',
            ]
        );
    }
}
