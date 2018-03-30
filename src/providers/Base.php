<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\providers;

use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Base extends Model implements SettingsInterface
{
    /**
     * @inheritdoc
     */
    public function toConfig(): array
    {
        return $this->toArray();
    }

    /**
     * @inheritdoc
     */
    public function inputHtml(): string
    {
        return '';
    }
}
