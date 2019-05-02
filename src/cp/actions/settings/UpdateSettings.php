<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\cp\actions\settings;

use Craft;
use flipbox\craft\ember\actions\models\CreateModel;
use flipbox\patron\models\Settings;
use flipbox\patron\Patron;
use yii\base\Model;
use yii\web\NotFoundHttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method array parentNormalizeSiteConfig($config = [])
 */
class UpdateSettings extends CreateModel
{
    public $validBodyParams = [
        'callbackUrlPath',
        'encryptStorageData',
        'autoPopulateTokenEnvironments',
        'applyProviderEnvironmentsToToken'
    ];

    /**
     * @inheritdoc
     */
    public $statusCodeSuccess = 200;

    /**
     * @param Model $model
     * @return bool
     * @throws \Throwable
     */
    protected function performAction(Model $model): bool
    {
        if (!$model instanceof Settings) {
            throw new NotFoundHttpException(sprintf(
                "Settings must be an instance of '%s', '%s' given.",
                Settings::class,
                get_class($model)
            ));
        }

        return Craft::$app->getPlugins()->savePluginSettings(
            Patron::getInstance(),
            $model->toArray()
        );
    }

    /**
     * @inheritdoc
     * @return Settings
     */
    protected function newModel(array $config = []): Model
    {
        return clone Patron::getInstance()->getSettings();
    }
}
