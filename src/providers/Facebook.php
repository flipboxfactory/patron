<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\providers;

use Craft;
use flipbox\ember\helpers\ModelHelper;
use League\OAuth2\Client\Provider\Facebook as FacebookProvider;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Facebook extends Base
{
    /**
     * @var string
     */
    public $graphApiVersion = 'v2.10';

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function inputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate(
            'patron/_settings/facebook',
            [
                'settings' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'graphApiVersion'
                    ],
                    'match',
                    'pattern' => FacebookProvider::GRAPH_API_VERSION_REGEX
                ],
                [
                    [
                        'graphApiVersion'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }
}
