<?php

namespace flipbox\patron\cp\controllers\view;

class GeneralController extends AbstractViewController
{
    /**
     * Index
     *
     * @return string
     */
    public function actionIndex()
    {
        // Empty variables for template
        $variables = [];

        // apply base view variables
        $this->baseVariables($variables);

        return $this->redirect(
            $variables['baseCpPath'] . '/providers'
        );
    }
}
