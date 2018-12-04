<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\cp\controllers;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\patron\cp\actions\settings\UpdateSettings;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class SettingsController extends AbstractController
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
                    'default' => 'save'
                ],
                'redirect' => [
                    'only' => ['save'],
                    'actions' => [
                        'save' => [200]
                    ]
                ],
                'flash' => [
                    'actions' => [
                        'save' => [
                            200 => Craft::t('patron', "Settings successfully updated."),
                            400 => Craft::t('patron', "Failed to update settings.")
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
            'save' => ['post', 'put']
        ];
    }

    /**
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSave()
    {
        /** @var UpdateSettings $action */
        $action = Craft::createObject([
            'class' => UpdateSettings::class,
            'checkAccess' => [$this, 'checkUpdateAccess']
        ], [
            'update',
            $this
        ]);

        return $action->runWithParams([]);
    }

    /**
     * @return bool
     * @throws \yii\web\ForbiddenHttpException
     */
    public function checkUpdateAccess(): bool
    {
        return $this->checkAdminAccess();
    }
}
