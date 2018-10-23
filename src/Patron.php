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
use flipbox\ember\modules\LoggerTrait;
use flipbox\patron\migrations\m181019_220655_provider_instances;
use flipbox\patron\models\Settings as SettingsModel;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method SettingsModel getSettings()
 *
 * @property services\Providers $providers
 * @property services\ProviderSettings $providerSettings
 * @property services\ProviderLocks $providerLocks
 * @property services\Tokens $tokens
 * @property services\ManageProviders $manageProviders
 * @property services\ManageTokens $manageTokens
 * @property services\Session $session
 */
class Patron extends Plugin
{
    use LoggerTrait;

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
            'providers' => services\Providers::class,
            'providerSettings' => services\ProviderSettings::class,
            'providerLocks' => services\ProviderLocks::class,
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

        // Add default environments upon creation
        $defaultEnvironments = $this->getSettings()->getDefaultEnvironments();
        if (!empty($defaultEnvironments)) {
            Event::on(
                records\ProviderInstance::class,
                records\ProviderInstance::EVENT_BEFORE_INSERT,
                function ($event) use ($defaultEnvironments) {
                    /** @var records\ProviderInstance $provider */
                    $provider = $event->sender;

                    // Ignore if already set
                    if ($provider->isRelationPopulated('environments') === true) {
                        return;
                    }

                    $provider->setEnvironments($defaultEnvironments);
                    $provider->autoSaveEnvironments = true;
                }
            );
        }

        // Replicate environments to token
        if ($this->getSettings()->applyProviderEnvironmentsToToken === true) {
            Event::on(
                records\Token::class,
                records\Token::EVENT_BEFORE_INSERT,
                function ($event) {
                    /** @var records\Token $token */
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
            );
        }
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
     * @return services\ProviderSettings
     */
    public function getProviderSettings(): services\ProviderSettings
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('providerSettings');
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return services\ProviderLocks
     */
    public function getProviderLocks(): services\ProviderLocks
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('providerLocks');
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

                'patron' =>
                    'patron/cp/view/general/index',

                'patron/providers' =>
                    'patron/cp/view/providers/default/index',

                'patron/providers/new' =>
                    'patron/cp/view/providers/default/upsert',

                'patron/providers/<identifier:\d+>' =>
                    'patron/cp/view/providers/default/upsert',

                'patron/providers/<provider:\d+>/instances/<identifier:\d+>' =>
                    'patron/cp/view/providers/instances/upsert',

                'patron/providers/<provider:\d+>/instances/new' =>
                    'patron/cp/view/providers/instances/upsert',

                'patron/providers/<provider:\d+>/tokens' =>
                    'patron/cp/view/providers/tokens/index',
            ]
        );
    }
}
