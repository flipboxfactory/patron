<?php

namespace flipbox\patron\cp;

use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use Flipbox\OAuth2\Client\Provider\Guardian;
use flipbox\patron\events\RegisterProviderIcon;
use flipbox\patron\events\RegisterProviders;
use flipbox\patron\events\RegisterProviderSettings;
use flipbox\patron\Patron;
use flipbox\patron\settings\FacebookSettings;
use flipbox\patron\settings\GuardianSettings;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Instagram;
use League\OAuth2\Client\Provider\LinkedIn;
use yii\base\Event;
use yii\base\Module;
use yii\web\NotFoundHttpException;

/**
 * @property Patron $module
 */
class Cp extends Module
{
    /**
     * @var array
     */
    private $icons = [];

    /**
     * @var array
     */
    protected $defaultIcons = [
        Google::class => '@vendor/flipboxfactory/patron/src/icons/google.svg',
        LinkedIn::class => '@vendor/flipboxfactory/patron/src/icons/linkedin.svg',
        Facebook::class => '@vendor/flipboxfactory/patron/src/icons/facebook.svg',
        Instagram::class => '@vendor/flipboxfactory/patron/src/icons/instagram.svg',
        Github::class => '@vendor/flipboxfactory/patron/src/icons/github.svg',
        Guardian::class => '@vendor/flipboxfactory/patron/src/icons/guardian.svg',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Components
        $this->setComponents([
            'settings' => services\Settings::class
        ]);

        // Register settings for providers
        RegisterProviderSettings::on(
            Guardian::class,
            RegisterProviderSettings::REGISTER_SETTINGS,
            function (RegisterProviderSettings $event) {
                $event->class = GuardianSettings::class;
            }
        );

        RegisterProviderSettings::on(
            Facebook::class,
            RegisterProviderSettings::REGISTER_SETTINGS,
            function (RegisterProviderSettings $event) {
                $event->class = FacebookSettings::class;
            }
        );

        // Ember templates
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $e) {
                $e->roots['patron/ember/card'] = Craft::$app->getPath()->getVendorPath() .
                    '/flipboxfactory/craft-assets-card/src/templates';
                $e->roots['patron/ember/circle-icon'] = Craft::$app->getPath()->getVendorPath() .
                    '/flipboxfactory/craft-assets-circle-icon/src/templates';
            }
        );
    }

    /**
     * @inheritdoc
     * @throws NotFoundHttpException
     */
    public function beforeAction($action)
    {
        if (!Craft::$app->request->getIsCpRequest()) {
            throw new NotFoundHttpException();
        }

        return parent::beforeAction($action);
    }


    /*******************************************
     * SERVICES
     *******************************************/

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return services\Settings
     */
    public function getSettings(): services\Settings
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('settings');
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        $event = new RegisterProviders([
            'providers' => [
                LinkedIn::class,
                Facebook::class,
                Instagram::class,
                Google::class,
                Github::class
            ]
        ]);

        $this->trigger(
            $event::REGISTER_PROVIDERS,
            $event
        );

        return $event->providers;
    }

    /**
     * @param string $class
     * @return string|null
     */
    public function getProviderIcon(string $class)
    {
        if (!array_key_exists($class, $this->icons)) {
            $event = new RegisterProviderIcon([
                'icon' => $this->defaultIcons[$class] ?? null
            ]);

            Event::trigger(
                $class,
                RegisterProviderIcon::REGISTER_ICON,
                $event
            );

            $this->icons[$class] = $event->icon;
        }

        return $this->icons[$class];
    }
}
