<?php

namespace flipbox\patron\cp\controllers\view\providers;

use Craft;
use craft\helpers\UrlHelper;
use flipbox\patron\cp\Cp;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;

/**
 * @property Cp $module
 */
abstract class AbstractViewController extends \flipbox\patron\cp\controllers\view\AbstractViewController
{
    /**
     * The index view template path
     */
    const TEMPLATE_BASE = 'patron/_cp/provider';

    /*******************************************
     * ENVIRONMENTS
     *******************************************/

    /**
     * @param Provider $provider
     * @return array
     */
    protected function availableEnvironments(Provider $provider): array
    {
        $usedEnvironments = array_keys($provider->environments);
        $allEnvironments = Patron::getInstance()->getSettings()->getEnvironments();

        return array_diff($allEnvironments, $usedEnvironments);
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

        $baseUrl = Craft::$app->getRequest()->getSegment(1) . '/' .
            Craft::$app->getRequest()->getSegment(2) . '/' .
            Craft::$app->getRequest()->getSegment(3);

        return [
            'general' => [
                'label' => Craft::t('patron', 'General'),
                'url' => UrlHelper::url($baseUrl . '/')
            ],
            'tokens' => [
                'label' => Craft::t('patron', 'Tokens'),
                'url' => UrlHelper::url($baseUrl . '/tokens')
            ]
        ];
    }

    /*******************************************
     * PATHS
     *******************************************/

    /**
     * @return string
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/providers';
    }

    /**
     * @return string
     */
    protected function getBaseCpPath(): string
    {
        return parent::getBaseCpPath() . '/providers';
    }

    /*******************************************
     * VARIABLES
     *******************************************/

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
            'url' => UrlHelper::url(Patron::getInstance()->getUniqueId() . '/providers')
        ];
    }

    /**
     * @param array $variables
     */
    protected function insertVariables(array &$variables)
    {
        // apply base view variables
        $this->baseVariables($variables);

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/{id}');

        // Append title
        $variables['title'] .= ' - ' . Craft::t('patron', 'New');

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t('patron', 'New'),
            'url' => UrlHelper::url($variables['baseCpPath'] . '/new')
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
        $variables['title'] .= ' - ' . $provider->getDisplayName();

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => $provider->getDisplayName(),
            'url' => UrlHelper::url(
                Patron::getInstance()->getUniqueId() . '/providers/' . $provider->getId()
            )
        ];
    }
}
