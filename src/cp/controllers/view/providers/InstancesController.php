<?php

namespace flipbox\patron\cp\controllers\view\providers;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\UrlHelper;
use flipbox\craft\assets\circleicon\CircleIcon;
use flipbox\ember\web\assets\rowinfomodal\RowInfoModal;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderInstance;

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
     * @param null $provider
     * @param null $identifier
     * @param ProviderInstance|null $instance
     * @return \yii\web\Response
     * @throws \flipbox\ember\exceptions\NotFoundException
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

        // Template variables
        $this->instanceVariables($variables, $provider);

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


    /*******************************************
     * VARIABLES
     *******************************************/

    /**
     * @param array $variables
     * @param Provider $provider
     */
    protected function instanceVariables(array &$variables, Provider $provider)
    {
        $this->updateVariables($variables, $provider);

        $variables['title'] .= ' ' . Craft::t('patron', "Instance");

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t('patron', "Instance"),
            'url' => UrlHelper::url(
                $variables['baseCpPath'] . '/instances/' .
                Craft::$app->getRequest()->getSegment(5)
            )
        ];
    }
}
