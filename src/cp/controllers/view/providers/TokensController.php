<?php

namespace flipbox\patron\cp\controllers\view\providers;

use Craft;
use craft\helpers\UrlHelper;
use flipbox\craft\assets\circleicon\CircleIcon;
use flipbox\ember\web\assets\rowinfomodal\RowInfoModal;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
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
     * @param int|null $provider
     * @return \yii\web\Response
     * @throws \flipbox\ember\exceptions\NotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex($provider = null)
    {
        Craft::$app->getView()->registerAssetBundle(CircleIcon::class);
        Craft::$app->getView()->registerAssetBundle(RowInfoModal::class);

        // Empty variables for template
        $variables = [];

        $provider = Patron::getInstance()->manageProviders()->getByCondition([
            'id' => $provider,
            'enabled' => null
        ]);

        // Template variables
        $this->tokenVariables($variables, $provider);

        $variables['provider'] = $provider;

        // Tabs
        $variables['tabs'] = $this->getTabs($provider);
        $variables['selectedTab'] = 'tokens';

        return $this->renderTemplate(static::TEMPLATE_INDEX, $variables);
    }

    /*******************************************
     * PATHS
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
        return parent::getBaseActionPath() . '/tokens';
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
        $this->updateVariables($variables, $provider);

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/tokens');

        $variables['title'] .= ' ' . Craft::t('patron', "Tokens");

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t('patron', "Tokens"),
            'url' => UrlHelper::url(
                $variables['baseCpPath'] . '/tokens'
            )
        ];
    }
}
