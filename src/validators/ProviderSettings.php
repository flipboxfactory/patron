<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\validators;

use Craft;
use flipbox\patron\records\ProviderInstance;
use yii\base\InvalidArgumentException;
use yii\validators\Validator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ProviderSettings extends Validator
{
    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function validateAttribute($model, $attribute)
    {
        if (!$model instanceof ProviderInstance) {
            throw new InvalidArgumentException(sprintf(
                "Model must be an instance of %s, %s given.",
                ProviderInstance::class,
                get_class($model)
            ));
        }

        if (!$model->getProviderSettings()->validate()) {
            $message = Craft::t(
                'patron',
                'Invalid settings.'
            );
            $this->addError($model, $attribute, $message);
        }
    }
}
