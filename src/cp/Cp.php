<?php

namespace flipbox\patron\cp;

use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use flipbox\patron\events\RegisterProviderIcons;
use flipbox\patron\events\RegisterProviders;
use flipbox\patron\events\RegisterProviderSettings;
use flipbox\patron\Patron;
use flipbox\patron\settings\FacebookSettings;
use League\OAuth2\Client\Provider\Facebook;
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
    private $icons;

    /**
     * @var array
     */
    private $providers;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

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
     * PROVIDERS
     *******************************************/

    /**
     * @return array
     */
    public function getProviders(): array
    {
        if ($this->providers === null) {
            $event = new RegisterProviders();

            $this->trigger(
                $event::REGISTER_PROVIDERS,
                $event
            );

            $this->providers = $event->providers;
        }

        return $this->providers;
    }

    /**
     * @return array
     */
    public function getProviderIcons(): array
    {
        if ($this->icons === null) {
            $event = new RegisterProviderIcons();

            $this->trigger(
                $event::REGISTER_ICON,
                $event
            );

            $this->icons = $event->icons;
        }

        return $this->icons;
    }
}
