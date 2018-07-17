<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\cp\controllers\view;

use Craft;
use craft\helpers\UrlHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class SettingsController extends AbstractViewController
{
    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = AbstractViewController::TEMPLATE_BASE . '/settings';

    /**
     * Index
     *
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $variables = [];
        $this->baseVariables($variables);

        $variables['fullPageForm'] = true;

        return $this->renderTemplate(static::TEMPLATE_INDEX, $variables);
    }


    /*******************************************
     * BASE PATHS
     *******************************************/

    /**
     * @return string
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/settings';
    }

    /**
     * @return string
     */
    protected function getBaseCpPath(): string
    {
        return parent::getBaseCpPath() . '/settings';
    }

    /*******************************************
     * VARIABLES
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function baseVariables(array &$variables = [])
    {
        parent::baseVariables($variables);

        $variables['crumbs'][] = [
            'label' => Craft::t('patron', 'Settings'),
            'url' => UrlHelper::url($variables['baseCpPath'])
        ];
    }
}
