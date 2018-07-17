<?php

namespace flipbox\patron\cp\controllers\view;

use Craft;
use craft\helpers\UrlHelper;
use flipbox\craft\assets\card\Card;
use flipbox\craft\assets\circleicon\CircleIcon;
use flipbox\ember\web\assets\rowinfomodal\RowInfoModal;
use flipbox\patron\helpers\ProviderHelper as ProviderHelper;
use flipbox\patron\records\Provider;
use flipbox\patron\services\ManageProviders as ProviderService;
use flipbox\patron\web\assets\providerswitcher\ProviderSwitcherAsset;

class ProvidersController extends AbstractViewController
{

    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = AbstractViewController::TEMPLATE_BASE . DIRECTORY_SEPARATOR . 'provider';

    /**
     * The upsert view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_INDEX . DIRECTORY_SEPARATOR . 'upsert';

    /**
     * The token view template path
     */
    const TEMPLATE_TOKEN = self::TEMPLATE_INDEX . DIRECTORY_SEPARATOR . 'tokens';

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
     * @throws \flipbox\ember\exceptions\NotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpsert($identifier = null, Provider $provider = null)
    {
        $this->getView()->registerAssetBundle(ProviderSwitcherAsset::class);

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

        // Full page form in the CP
        $variables['fullPageForm'] = true;

        // Tabs
        $variables['tabs'] = $this->getTabs($provider);
        $variables['selectedTab'] = 'general';

        return $this->renderTemplate(static::TEMPLATE_UPSERT, $variables);
    }

    /**
     * @param null $identifier
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTokens($identifier = null)
    {

        Craft::$app->getView()->registerAssetBundle(CircleIcon::class);
        Craft::$app->getView()->registerAssetBundle(RowInfoModal::class);

        // Empty variables for template
        $variables = [];

        $provider = $this->providerService()->getByCondition([
            'id' => $identifier,
            'environment' => null
        ]);

        $this->tokenVariables($variables, $provider);

        $variables['provider'] = $provider;

        // Tabs
        $variables['tabs'] = $this->getTabs($variables['provider']);
        $variables['selectedTab'] = 'tokens';

        return $this->renderTemplate(static::TEMPLATE_TOKEN, $variables);
    }


    /*******************************************
     * TABS
     *******************************************/

    /**
     * @param Provider $provider
     * @return array
     */
    protected function getTabs(Provider $provider): array
    {
        if ($provider->getId() === null) {
            return [];
        }

        $baseUrl = $this->getBaseCpPath() . '/' . $provider->getId();

        return [
            'general' => [
                'label' => Craft::t('patron', 'General'),
                'url' => UrlHelper::url($baseUrl . '/')
            ],
            'tokens' => [
                'label' => Craft::t('patron', 'Tokens'),
                'url' => UrlHelper::url($baseUrl . '/tokens')
            ],
//            'activity' => [
//                'label' => Craft::t('patron', 'Activity'),
//                'url' => UrlHelper::url($baseUrl.'/activity')
//            ]
        ];
    }

    /*******************************************
     * BASE VARIABLES
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function getBaseCpPath(): string
    {
        return parent::getBaseCpPath() . '/providers';
    }

    /**
     * @inheritdoc
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/providers';
    }

    /**
     * Set base variables used to generate template views
     *
     * @param array $variables
     */
    protected function baseVariables(array &$variables = [])
    {
        parent::baseVariables($variables);

        // Page title
        $variables['title'] .= ': ' . Craft::t('patron', "Providers");

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t('patron', "Providers"),
            'url' => UrlHelper::url($variables['baseCpPath'])
        ];
    }


    /*******************************************
     * UPDATE VARIABLES
     *******************************************/

    /**
     * @param array $variables
     * @param Provider $provider
     */
    protected function updateVariables(array &$variables, Provider $provider)
    {
        // apply base view variables
        $this->baseVariables($variables);


        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $provider->getId());

        // Append title
        $variables['title'] .= ' - ' . Craft::t('patron', 'Edit') . ' ' . $provider->getDisplayName();

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t(
                'patron',
                "Edit"
            ) . ": " . $provider->getDisplayName(),
            'url' => UrlHelper::url(
                $variables['baseCpPath'] . '/' . $provider->getId()
            )
        ];
    }

    /*******************************************
     * TOKEN VARIABLES
     *******************************************/

    /**
     * @param array $variables
     * @param Provider $provider
     */
    protected function tokenVariables(array &$variables, Provider $provider)
    {
        // apply base view variables
        $this->baseVariables($variables);

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $provider->getId() . '/tokens');
        $variables['baseActionPath'] = $this->module->uniqueId . ('/tokens');

        // Append title
        $variables['title'] .= ' - ' . $provider->getDisplayName() . ' ' . Craft::t('patron', 'Tokens');

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t(
                'patron',
                "Edit"
            ) . ": " . $provider->getDisplayName(),
            'url' => UrlHelper::url(
                $variables['baseCpPath'] . '/' . $provider->getId()
            )
        ];
    }
}
