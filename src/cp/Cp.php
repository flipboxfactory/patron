<?php

namespace flipbox\patron\cp;

use Craft;
use flipbox\patron\events\RegisterProviderInfo;
use flipbox\patron\events\RegisterProviders;
use flipbox\patron\events\RegisterProviderSettings;
use flipbox\patron\helpers\ProviderHelper;
use flipbox\patron\Patron;
use flipbox\patron\settings\FacebookSettings;
use League\OAuth2\Client\Provider\Facebook;
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
    private $info;

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
     * This should give is some additional information about the provider.
     *
     * ```
     * [
     *      Provider::class => [
     *          'name' => 'Provider Name',
     *          'icon' => 'path/to/icon.svg'
     *      ]
     * ]
     * ```
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getProviderInfo(): array
    {
        if ($this->info === null) {
            $event = new RegisterProviderInfo();

            $this->trigger(
                $event::REGISTER_INFO,
                $event
            );

            $this->info = $event->info;

            $this->formatInfoArray($this->info);
        }

        return $this->info;
    }

    /**
     * @param array $providers
     * @throws \ReflectionException
     */
    private function formatInfoArray(array &$providers)
    {
        foreach ($providers as $class => &$provider) {
            if (is_string($provider)) {
                $provider = [
                    'icon' => $provider
                ];
            }

            $provider['name'] = $provider['name'] ?? ProviderHelper::displayName($class);
        }
    }
}
