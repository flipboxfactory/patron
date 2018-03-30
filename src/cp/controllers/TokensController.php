<?php

namespace flipbox\patron\cp\controllers;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\patron\actions\token\Delete;
use flipbox\patron\actions\token\Disable;
use flipbox\patron\actions\token\Enable;
use flipbox\patron\Patron;

class TokensController extends AbstractController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'error' => [
                    'default' => 'provider'
                ],
                'redirect' => [
                    'only' => ['delete', 'enable', 'disable'],
                    'actions' => [
                        'delete' => [204],
                        'enable' => [200],
                        'disable' => [200]
                    ]
                ],
                'flash' => [
                    'actions' => [
                        'delete' => [
                            204 => Craft::t('patron', "Token successfully deleted."),
                            401 => Craft::t('patron', "Failed to delete token.")
                        ],
                        'enable' => [
                            200 => Craft::t('patron', "Token successfully enabled."),
                            401 => Craft::t('patron', "Failed to enabled token.")
                        ],
                        'disable' => [
                            200 => Craft::t('patron', "Token successfully disable."),
                            401 => Craft::t('patron', "Failed to disable token.")
                        ],
                    ]
                ]
            ]
        );
    }

    /**
     * @return array
     */
    public function verbs(): array
    {
        return [
            'enable' => ['post'],
            'disable' => ['post'],
            'delete' => ['post', 'delete']
        ];
    }

    /**
     * @param null $token
     * @return array
     */
    public function actionModal($token = null): array
    {
        if (null === $token) {
            $token = Craft::$app->getRequest()->getBodyParam('token');
        }

        $token = Patron::getInstance()->manageTokens()->get($token);

        $view = $this->getView();
        return [
            'html' => Patron::getInstance()->getSettings()->getTokenView()->render([
                'token' => $token
            ]),
            'headHtml' => $view->getHeadHtml(),
            'footHtml' => $view->getBodyHtml()
        ];
    }

    /**
     * @param null $token
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDisable($token = null)
    {
        if (null === $token) {
            $token = Craft::$app->getRequest()->getRequiredBodyParam('token');
        }

        $action = Craft::createObject([
            'class' => Disable::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'revoke',
            $this
        ]);

        return $action->runWithParams([
            'token' => $token
        ]);
    }

    /**
     * @param null $token
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionEnable($token = null)
    {
        if (null === $token) {
            $token = Craft::$app->getRequest()->getRequiredBodyParam('token');
        }

        $action = Craft::createObject([
            'class' => Enable::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'reinstate',
            $this
        ]);

        return $action->runWithParams([
            'token' => $token
        ]);
    }

    /**
     * @param null $token
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDelete($token = null)
    {
        if (null === $token) {
            $token = Craft::$app->getRequest()->getRequiredBodyParam('token');
        }

        $action = Craft::createObject([
            'class' => Delete::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'delete',
            $this
        ]);

        return $action->runWithParams([
            'token' => $token
        ]);
    }
}
