<?php

namespace flipbox\patron\cp\controllers\view\providers;

use Craft;
use craft\helpers\UrlHelper;
use flipbox\craft\assets\circleicon\CircleIcon;
use flipbox\craft\ember\exceptions\NotFoundException;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
use flipbox\patron\records\Token;
use flipbox\patron\web\assets\providerswitcher\ProvidersAsset;

class TokensController extends AbstractViewController
{
    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = AbstractViewController::TEMPLATE_BASE . '/tokens';

    /**
     * The index view template path
     */
    const TEMPLATE_INSERT = self::TEMPLATE_INDEX . '/index';

    /**
     * The upsert view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_INDEX . '/upsert';

    /**
     * @param int|string $provider
     * @return \yii\web\Response
     * @throws NotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex($provider)
    {
        Craft::$app->getView()->registerAssetBundle(CircleIcon::class);

        // Empty variables for template
        $variables = [];

        $provider = Provider::getOne([
            'id' => $provider,
            'enabled' => null,
            'environment' => null
        ]);

        // Template variables
        $this->tokenVariables($variables, $provider);

        $variables['provider'] = $provider;

        // Tabs
        $variables['tabs'] = $this->getTabs($provider);
        $variables['selectedTab'] = 'tokens';

        return $this->renderTemplate(static::TEMPLATE_INDEX, $variables);
    }

    /**
     * @param int|string $provider
     * @param int|string $identifier
     * @param Token|null $token
     * @return \yii\web\Response
     * @throws NotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpsert($provider, $identifier, Token $token = null)
    {
        Craft::$app->getView()->registerAssetBundle(CircleIcon::class);

        // Empty variables for template
        $variables = [];

        $provider = Provider::getOne([
            'id' => $provider,
            'enabled' => null,
            'environment' => null
        ]);

        if (null === $token) {
            $token = Token::getOne([
                is_numeric($identifier) ? 'id' : 'accessToken' => $identifier,
                'enabled' => null,
                'environment' => null
            ]);
        }

        // Template variables
        $this->tokenUpdateVariables($variables, $provider, $token);

        $availableEnvironments = array_merge(
            $this->availableTokenEnvironments($token),
            $token->getEnvironments()
                ->indexBy(null)
                ->select(['environment'])
                ->column()
        );

        $environmentOptions = [];
        foreach (Patron::getInstance()->getSettings()->getEnvironments() as $env) {
            $environmentOptions[] = [
                'label' => Craft::t('patron', $env),
                'value' => $env,
                'disabled' => !in_array($env, $availableEnvironments, true)
            ];
        }

        $variables['provider'] = $provider;
        $variables['token'] = $token;
        $variables['environmentOptions'] = $environmentOptions;

        // Full page form in the CP
        $variables['fullPageForm'] = true;

        // Tabs
        $variables['tabs'] = $this->getTabs($provider);
        $variables['selectedTab'] = 'tokens';

        return $this->renderTemplate(static::TEMPLATE_UPSERT, $variables);
    }

    /**
     * @param Token $token
     * @return array
     */
    protected function availableTokenEnvironments(Token $token): array
    {
        $usedEnvironments = array_keys($token->environments);
        $allEnvironments = Patron::getInstance()->getSettings()->getEnvironments();

        return array_diff($allEnvironments, $usedEnvironments);
    }

    /*******************************************
     * PATHS
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function getBaseCpPath(): string
    {
        return parent::getBaseCpPath() . '/' . Craft::$app->getRequest()->getSegment(3) .
            '/' . Craft::$app->getRequest()->getSegment(4);
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
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl();

        // Title
        $variables['title'] .= ' ' . Craft::t('patron', "Tokens");

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t('patron', "Tokens"),
            'url' => UrlHelper::url(
                $variables['baseCpPath']
            )
        ];
    }

    /**
     * @param array $variables
     * @param Provider $provider
     */
    protected function tokenUpdateVariables(array &$variables, Provider $provider, Token $token)
    {
        $this->tokenVariables($variables, $provider);

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $token->getId());

        $variables['title'] .= ' ' . Craft::t('patron', "Edit");

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t('patron', "Edit"),
            'url' => UrlHelper::url(
                $variables['baseCpPath'] . '/' . $token->getId()
            )
        ];
    }
}
