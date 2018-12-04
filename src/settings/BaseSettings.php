<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\settings;

use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class BaseSettings extends Model implements SettingsInterface
{
    /**
     * @inheritdoc
     */
    public function inputHtml(): string
    {
        return '';
    }
}
