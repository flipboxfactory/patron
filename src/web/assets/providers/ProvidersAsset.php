<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\web\assets\providers;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ProvidersAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = __DIR__ . '/dist';

    /**
     * @inheritdoc
     */
    public $depends = [
        CpAsset::class,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->css = [
            'css/Providers.css'
        ];

        parent::init();
    }
}
