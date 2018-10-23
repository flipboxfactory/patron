<?php

namespace flipbox\patron\cp\controllers\view\providers;

use Craft;
use craft\helpers\UrlHelper;
use flipbox\craft\assets\card\Card;
use flipbox\craft\assets\circleicon\CircleIcon;
use flipbox\ember\web\assets\rowinfomodal\RowInfoModal;
use flipbox\patron\helpers\ProviderHelper as ProviderHelper;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderInstance;
use flipbox\patron\services\ManageProviders as ProviderService;
use flipbox\patron\web\assets\providerswitcher\ProvidersAsset;

class TokensController extends AbstractViewController
{
    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = AbstractViewController::TEMPLATE_BASE . '/tokens';

    /**
     * The upsert view template path
     */
    const TEMPLATE_INSERT = self::TEMPLATE_INDEX . '/index';

    /**
     * @param null $identifier
     * @return \yii\web\Response
     * @throws \flipbox\ember\exceptions\NotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex($identifier = null)
    {
        Craft::$app->getView()->registerAssetBundle(CircleIcon::class);
        Craft::$app->getView()->registerAssetBundle(RowInfoModal::class);

        // Empty variables for template
        $variables = [];

        $provider = Patron::getInstance()->manageProviders()->getByCondition([
            'id' => $identifier,
            'environment' => null,
            'enabled' => null
        ]);

        $this->tokenVariables($variables, $provider);

        $variables['provider'] = $provider;

        // Tabs
        $variables['tabs'] = $this->getTabs($provider);
        $variables['selectedTab'] = 'tokens';

        return $this->renderTemplate(static::TEMPLATE_INDEX, $variables);
    }

    /*******************************************
     * BASE VARIABLES
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function getBaseCpPath(): string
    {
        return parent::getBaseCpPath() . '/' . Craft::$app->getRequest()->getSegment(3);
    }

    /**
     * @inheritdoc
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/instances';
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
     * VARIABLES
     *******************************************/

    /**
     * @param array $variables
     * @param Provider $provider
     */
    protected function tokenVariables(array &$variables, Provider $provider)
    {
        // apply base view variables
        $this->baseVariables($variables);

//        // Set the "Continue Editing" URL
//        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $provider->getId() . '/tokens');
//        $variables['baseActionPath'] = $this->module->uniqueId . ('/tokens');
//
//        // Append title
//        $variables['title'] .= ' - ' . $provider->getDisplayName() . ' ' . Craft::t('patron', 'Tokens');
//
//        // Breadcrumbs
//        $variables['crumbs'][] = [
//            'label' => Craft::t(
//                    'patron',
//                    "Edit"
//                ) . ": " . $provider->getDisplayName(),
//            'url' => UrlHelper::url(
//                $variables['baseCpPath'] . '/' . $provider->getId()
//            )
//        ];
    }
}
