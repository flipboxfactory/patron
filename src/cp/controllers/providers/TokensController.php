<?php

namespace flipbox\patron\cp\controllers\providers;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\patron\actions\token\Delete;
use flipbox\patron\actions\token\Disable;
use flipbox\patron\actions\token\Enable;
use flipbox\patron\actions\token\Update;
use flipbox\patron\cp\controllers\AbstractController;
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
                    'only' => ['update', 'delete', 'enable', 'disable'],
                    'actions' => [
                        'update' => [200],
                        'delete' => [204],
                        'enable' => [200],
                        'disable' => [200]
                    ]
                ],
                'flash' => [
                    'actions' => [
                        'update' => [
                            200 => Craft::t('patron', "Token successfully updated."),
                            400 => Craft::t('patron', "Failed to updated token.")
                        ],
                        'delete' => [
                            204 => Craft::t('patron', "Token successfully deleted."),
                            400 => Craft::t('patron', "Failed to delete token.")
                        ],
                        'enable' => [
                            200 => Craft::t('patron', "Token successfully enabled."),
                            400 => Craft::t('patron', "Failed to enabled token.")
                        ],
                        'disable' => [
                            200 => Craft::t('patron', "Token successfully disable."),
                            400 => Craft::t('patron', "Failed to disable token.")
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
            'update' => ['post'],
            'enable' => ['post'],
            'disable' => ['post'],
            'delete' => ['post', 'delete']
        ];
    }

    /**
     * @param null $token
     * @return array
     * @throws \flipbox\ember\exceptions\NotFoundException
     */
    public function actionModal($token = null): array
    {
        if (null === $token) {
            $token = Craft::$app->getRequest()->getBodyParam('token');
        }

        $view = $this->getView();
        return [
            'html' => Patron::getInstance()->getSettings()->getTokenView()->render([
                'token' => Patron::getInstance()->manageTokens()->get($token)
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
    public function actionUpdate($token = null)
    {
        if (null === $token) {
            $token = Craft::$app->getRequest()->getRequiredBodyParam('token');
        }

        $action = Craft::createObject([
            'class' => Update::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'update',
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
