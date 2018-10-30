<?php

namespace flipbox\patron\cp\controllers\view\providers;

use Craft;
use flipbox\craft\assets\card\Card;
use flipbox\craft\assets\circleicon\CircleIcon;
use flipbox\patron\helpers\ProviderHelper as ProviderHelper;
use flipbox\patron\records\Provider;
use flipbox\patron\services\ManageProviders as ProviderService;
use flipbox\patron\web\assets\providers\ProvidersAsset;

class DefaultController extends AbstractViewController
{
    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = AbstractViewController::TEMPLATE_BASE;

    /**
     * The upsert view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_INDEX . '/upsert';

    /**
     * @return ProviderService
     */
    protected function providerService(): ProviderService
    {
        return $this->module->module->manageProviders();
    }

    /**
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        Craft::$app->getView()->registerAssetBundle(Card::class);
        Craft::$app->getView()->registerAssetBundle(CircleIcon::class);

        // Empty variables for template
        $variables = [];

        // apply base view variables
        $this->baseVariables($variables);

        // Full page form in the CP
        $variables['fullPageForm'] = true;

        // Configured providers
        $variables['providers'] = $this->providerService()->findAll();

        return $this->renderTemplate(static::TEMPLATE_INDEX, $variables);
    }

    /**
     * @param null $identifier
     * @param Provider|null $provider
     * @return \yii\web\Response
     * @throws \craft\errors\InvalidPluginException
     * @throws \flipbox\ember\exceptions\NotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpsert($identifier = null, Provider $provider = null)
    {
        $this->getView()->registerAssetBundle(ProvidersAsset::class);

        // Empty variables for template
        $variables = [];

        if (null === $provider) {
            if (null === $identifier) {
                $provider = $this->providerService()->create();
            } else {
                $provider = $this->providerService()->get($identifier);
            }
        }

        // Template variables
        if (!$provider->getId()) {
            $this->insertVariables($variables);
        } else {
            $this->updateVariables($variables, $provider);
        }

        // Available providers options
        $providerOptions = [];
        $providers = $this->module->getProviders();
        foreach ($providers as $availableProvider) {
            $providerOptions[] = [
                'label' => ProviderHelper::displayName($availableProvider),
                'value' => $availableProvider
            ];
        }
        $variables['providerOptions'] = $providerOptions;
        $variables['provider'] = $provider;

        $pluginLocks = [];
        $pluginHandles = $provider->getLocks()
            ->alias('locks')
            ->leftJoin('{{%plugins}} plugins', 'plugins.id=locks.pluginId')
            ->select(['handle'])->column();

        foreach ($pluginHandles as $pluginHandle) {
            $pluginLocks[] = array_merge(
                Craft::$app->getPlugins()->getPluginInfo($pluginHandle),
                [
                    'icon' => Craft::$app->getPlugins()->getPluginIconSvg($pluginHandle)
                ]
            );
        }

        // Plugins that have locked this provider
        $variables['pluginLocks'] = $pluginLocks;
        $variables['availableEnvironments'] = $this->availableEnvironments($provider);

        // Full page form in the CP
        $variables['fullPageForm'] = true;

        // Tabs
        $variables['tabs'] = $this->getTabs($provider);
        $variables['selectedTab'] = 'general';
        $variables['baseActionInstancePath'] = $this->getBaseActionPath() . '/instances';

        return $this->renderTemplate(static::TEMPLATE_UPSERT, $variables);
    }
}
