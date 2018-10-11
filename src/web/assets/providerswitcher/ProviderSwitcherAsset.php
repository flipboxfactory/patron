<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\web\assets\providerswitcher;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ProviderSwitcherAsset extends AssetBundle
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
        $this->js = [
            'js/ProviderSwitcher' . $this->dotJs()
        ];

        $this->css = [
            'css/Provider.css'
        ];

        parent::init();
    }
}
