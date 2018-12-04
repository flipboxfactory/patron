<?php

namespace flipbox\patron\cp\controllers;

use flipbox\craft\ember\filters\FlashMessageFilter;
use flipbox\craft\ember\filters\ModelErrorFilter;
use flipbox\craft\ember\filters\RedirectFilter;
use flipbox\patron\cp\Cp;
use yii\helpers\ArrayHelper;

/**
 * @property Cp $module
 */
abstract class AbstractController extends \flipbox\craft\ember\controllers\AbstractController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'redirect' => [
                    'class' => RedirectFilter::class
                ],
                'error' => [
                    'class' => ModelErrorFilter::class
                ],
                'flash' => [
                    'class' => FlashMessageFilter::class
                ]
            ]
        );
    }

    /**
     * @return bool
     * @throws \yii\web\ForbiddenHttpException
     */
    public function checkAdminAccess(): bool
    {
        $this->requireAdmin();
        return true;
    }
}
