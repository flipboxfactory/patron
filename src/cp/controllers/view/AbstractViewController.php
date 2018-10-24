<?php

namespace flipbox\patron\cp\controllers\view;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use flipbox\patron\cp\Cp;
use flipbox\patron\Patron;

/**
 * @property Cp $module
 */
abstract class AbstractViewController extends Controller
{
    /**
     * The index view template path
     */
    const TEMPLATE_BASE = 'patron/_cp';

    /*******************************************
     * VARIABLES
     *******************************************/

    /**
     * @return string
     */
    protected function getBaseActionPath(): string
    {
        return Patron::getInstance()->getUniqueId() . '/cp';
    }

    /**
     * @return string
     */
    protected function getBaseCpPath(): string
    {
        return Patron::getInstance()->getUniqueId();
    }

    /**
     * @param string $endpoint
     * @return string
     */
    protected function getBaseContinueEditingUrl(string $endpoint = ''): string
    {
        return $this->getBaseCpPath() . $endpoint;
    }

    /*******************************************
     * VARIABLES
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function baseVariables(array &$variables = [])
    {

        /** @var Patron $module */
        $module = Patron::getInstance();

        // Patron settings
        $variables['settings'] = $module->getSettings();

        // Page title
        $variables['title'] = Craft::t('patron', "Patron");

        // Selected tab
        $variables['selectedTab'] = '';

        // Path to controller actions
        $variables['baseActionPath'] = $this->getBaseActionPath();

        // Path to CP
        $variables['baseCpPath'] = $this->getBaseCpPath();

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $this->getBaseCpPath();

        // Select our sub-nav
        if (!$activeSubNav = Craft::$app->getRequest()->getSegment(2)) {
            $activeSubNav = 'providers';
        }
        $variables['selectedSubnavItem'] = 'patron.' . $activeSubNav;

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => $variables['title'],
            'url' => UrlHelper::url(Patron::getInstance()->getUniqueId())
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
}
