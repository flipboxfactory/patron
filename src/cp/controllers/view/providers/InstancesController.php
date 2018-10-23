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

class InstancesController extends AbstractViewController
{

    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = AbstractViewController::TEMPLATE_BASE . '/instances';

    /**
     * The upsert view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_INDEX . '/upsert';

    /**
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpsert($provider = null, $identifier = null, ProviderInstance $instance = null)
    {
        Craft::$app->getView()->registerAssetBundle(CircleIcon::class);
        Craft::$app->getView()->registerAssetBundle(RowInfoModal::class);

        $provider = Patron::getInstance()->manageProviders->getByCondition([
            'id' => $provider,
            'environment' => null,
            'enabled' => null
        ]);

        // Empty variables for template
        $variables = [];

        if (null === $instance) {
            if (null === $identifier) {
                $instance = new ProviderInstance();
            } else {
                $instance = ProviderInstance::findOne($identifier);
            }
        }

        $instance->setProvider($provider);

        /** @var ProviderInstance $instance */

        // Template variables
        if (!$instance->getId()) {
            $this->insertInstanceVariables($variables, $provider);
        } else {
            $this->updateInstanceVariables($variables, $provider, $instance);
        }

        $variables['provider'] = $provider;
        $variables['instance'] = $instance;
        $variables['availableEnvironments'] = $this->availableEnvironments($provider);

        // Full page form in the CP
        $variables['fullPageForm'] = true;

        // Tabs
        $variables['tabs'] = $this->getTabs($provider);
        $variables['selectedTab'] = 'general';

        return $this->renderTemplate(static::TEMPLATE_UPSERT, $variables);
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
    protected function insertInstanceVariables(array &$variables, Provider $provider)
    {
        $this->insertVariables($variables);
    }

    /**
     * @param array $variables
     * @param Provider $provider
     */
    protected function updateInstanceVariables(array &$variables, Provider $provider, ProviderInstance $instance)
    {
        $this->updateVariables($variables, $provider);
    }
}
