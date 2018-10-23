<?php

namespace flipbox\patron\cp\controllers;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\patron\actions\provider\Create;
use flipbox\patron\actions\provider\Delete;
use flipbox\patron\actions\provider\Disable;
use flipbox\patron\actions\provider\Enable;
use flipbox\patron\actions\provider\Update;

class ProvidersController extends AbstractController
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
                    'only' => ['create', 'update', 'delete', 'enable', 'disable'],
                    'actions' => [
                        'create' => [201],
                        'update' => [200],
                        'delete' => [204],
                        'enable' => [200],
                        'disable' => [200]
                    ]
                ],
                'flash' => [
                    'actions' => [
                        'create' => [
                            201 => Craft::t('patron', "Provider successfully created."),
                            400 => Craft::t('patron', "Failed to create provider.")
                        ],
                        'update' => [
                            200 => Craft::t('patron', "Provider successfully updated."),
                            400 => Craft::t('patron', "Failed to update provider.")
                        ],
                        'delete' => [
                            204 => Craft::t('patron', "Provider successfully deleted."),
                            400 => Craft::t('patron', "Failed to delete provider.")
                        ],
                        'enable' => [
                            200 => Craft::t('patron', "Provider successfully enabled."),
                            400 => Craft::t('patron', "Failed to enabled provider.")
                        ],
                        'disable' => [
                            200 => Craft::t('patron', "Provider successfully disable."),
                            400 => Craft::t('patron', "Failed to disable provider.")
                        ],
                    ]
                ]
            ]
        );
    }

    /**
     * @return array
     */
    protected function verbs(): array
    {
        return [
            'index' => ['get'],
            'view' => ['get'],
            'create' => ['post'],
            'update' => ['post', 'put'],
            'enable' => ['post'],
            'disable' => ['post'],
            'delete' => ['post', 'delete']
        ];
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'create' => [
                'class' => Create::class,
                'checkAccess' => [$this, 'checkAdminAccess']
            ]
        ];
    }

    /**
     * @param string|int|null $provider
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdate($provider = null)
    {
        if ($provider === null) {
            $provider = Craft::$app->getRequest()->getRequiredBodyParam('provider');
        }

        $action = Craft::createObject([
            'class' => Update::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'update',
            $this
        ]);

        return $action->runWithParams([
            'provider' => $provider
        ]);
    }

    /**
     * @param int|null $provider
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDelete(int $provider = null)
    {
        if ($provider === null) {
            $provider = Craft::$app->getRequest()->getRequiredBodyParam('provider');
        }

        $action = Craft::createObject([
            'class' => Delete::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'delete',
            $this
        ]);

        return $action->runWithParams([
            'provider' => $provider
        ]);
    }

    /**
     * @param int|null $provider
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionEnable(int $provider = null)
    {
        if ($provider === null) {
            $provider = Craft::$app->getRequest()->getRequiredBodyParam('provider');
        }

        $action = Craft::createObject([
            'class' => Enable::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'enable',
            $this
        ]);

        return $action->runWithParams([
            'provider' => $provider
        ]);
    }

    /**
     * @param int|null $provider
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDisable(int $provider = null)
    {
        if ($provider === null) {
            $provider = Craft::$app->getRequest()->getRequiredBodyParam('provider');
        }

        $action = Craft::createObject([
            'class' => Disable::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'disable',
            $this
        ]);

        return $action->runWithParams([
            'provider' => $provider
        ]);
    }
}
