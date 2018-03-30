<?php

namespace flipbox\patron\cp;

use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use Flipbox\OAuth2\Client\Provider\Guardian;
use flipbox\patron\events\RegisterProviders;
use flipbox\patron\Patron;
use flipbox\twig\TriggerExtension;
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
     * Event to register providers
     */
    const EVENT_REGISTER_PROVIDERS = 'registerProviders';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Add in our Twig extensions
        $twig = Craft::$app->getView()->getTwig();
        if (!$twig->hasExtension(TriggerExtension::class)) {
            $twig->addExtension(
                new TriggerExtension()
            );
        }

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
     */
    public function beforeAction($action)
    {
        if (!Craft::$app->request->getIsCpRequest()) {
            throw new NotFoundHttpException();
        }

        return parent::beforeAction($action);
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        $event = new RegisterProviders([
            'providers' => [
                Google::class,
                LinkedIn::class,
                Facebook::class,
                Instagram::class,
                Github::class,
                Guardian::class
            ]
        ]);

        $this->trigger(
            static::EVENT_REGISTER_PROVIDERS,
            $event
        );

        return $event->providers;
    }
}
