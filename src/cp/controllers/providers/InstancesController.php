<?php

namespace flipbox\patron\cp\controllers\providers;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\patron\actions\provider\CreateProviderInstance;
use flipbox\patron\actions\provider\DeleteProviderInstance;
use flipbox\patron\actions\provider\UpdateProviderInstance;
use flipbox\patron\cp\controllers\AbstractController;

class InstancesController extends AbstractController
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
                    'default' => 'instance'
                ],
                'redirect' => [
                    'only' => ['create', 'update', 'delete'],
                    'actions' => [
                        'create' => [201],
                        'update' => [200],
                        'delete' => [204],
                    ]
                ],
                'flash' => [
                    'actions' => [
                        'create' => [
                            201 => Craft::t('patron', "Provider instance successfully created."),
                            400 => Craft::t('patron', "Failed to create provider instance.")
                        ],
                        'update' => [
                            200 => Craft::t('patron', "Provider instance successfully updated."),
                            400 => Craft::t('patron', "Failed to update provider instance.")
                        ],
                        'delete' => [
                            204 => Craft::t('patron', "Provider instance successfully deleted."),
                            400 => Craft::t('patron', "Failed to delete provider instance.")
                        ]
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
            'create' => ['post'],
            'update' => ['post', 'put'],
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
                'class' => CreateProviderInstance::class,
                'checkAccess' => [$this, 'checkAdminAccess']
            ]
        ];
    }

    /**
     * @param string|int|null $instance
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdate($instance = null)
    {
        if ($instance === null) {
            $instance = Craft::$app->getRequest()->getRequiredBodyParam('instance');
        }

        $action = Craft::createObject([
            'class' => UpdateProviderInstance::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'update',
            $this
        ]);

        return $action->runWithParams([
            'instance' => $instance
        ]);
    }

    /**
     * @param string|int|null $instance
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDelete($instance = null)
    {
        if ($instance === null) {
            $instance = Craft::$app->getRequest()->getRequiredBodyParam('instance');
        }

        $action = Craft::createObject([
            'class' => DeleteProviderInstance::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'delete',
            $this
        ]);

        return $action->runWithParams([
            'instance' => $instance
        ]);
    }
}
