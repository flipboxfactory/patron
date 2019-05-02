<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\controllers;

use craft\helpers\ArrayHelper;
use flipbox\craft\ember\controllers\AbstractController;
use flipbox\craft\ember\filters\CallableFilter;
use flipbox\patron\actions\authorization\Callback;
use flipbox\patron\Patron;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class AuthorizationController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'redirect' => [
                    'class' => CallableFilter::class,
                    'actions' => [
                        'callback' => function () {
                            $redirectUrl = Patron::getInstance()->getSession()->getRedirectUrl();
                            Patron::getInstance()->getSession()->removeAll();

                            if ($redirectUrl) {
                                return $this->redirect($redirectUrl);
                            }
                        }
                    ]
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function verbs(): array
    {
        return [
            'callback' => ['get']
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'callback' => [
                'class' => Callback::class,
                'checkAccess' => [$this, 'checkCallbackAccess']
            ]
        ];
    }

    /**
     * @return bool
     */
    public function checkCallbackAccess()
    {
        $this->requireLogin();
        return true;
    }
}
