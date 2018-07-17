<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\models;

use Craft;
use craft\base\Model;
use craft\helpers\StringHelper;
use craft\validators\UriValidator;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\helpers\UrlHelper;
use flipbox\ember\views\Template;
use flipbox\ember\views\ViewInterface;
use yii\base\Exception;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Settings extends Model
{
    /**
     * The callback url path
     */
    const DEFAULT_CALLBACK_URL_PATH = 'patron/authorization/callback';

    /**
     * Tge callback url route
     */
    const DEFAULT_CALLBACK_ROUTE = self::DEFAULT_CALLBACK_URL_PATH;

    /**
     * @var string|null
     */
    private $callbackUrlPath;

    /**
     * @var string
     */
    private $providerOverrideFileName = 'providers';

    /**
     * @var array
     */
    private $environments = [];

    /**
     * @var string
     */
    private $environment = null;

    /*******************************************
     * ENVIRONMENTS
     *******************************************/

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        if ($this->environment === null) {
            $this->environment = Craft::$app->getConfig()->env;
        }

        return $this->environment;
    }

    /**
     * @param string $environment
     * @return $this
     */
    public function setEnvironment(string $environment)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @return array
     */
    public function getEnvironments(): array
    {
        if (empty($this->environments)) {
            $this->environments[] = Craft::$app->getConfig()->env;
        }

        return $this->environments;
    }

    /**
     * @param array $environments
     * @return $this
     */
    public function setEnvironments(array $environments)
    {
        $this->environments = $environments;
        return $this;
    }

    /*******************************************
     * PROVIDER OVERRIDE CONFIG
     *******************************************/

    /**
     * @param string $providerOverrideFileName
     * @return string
     */
    public function setProviderOverrideFileName(string $providerOverrideFileName): string
    {
        $this->providerOverrideFileName = $providerOverrideFileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getProviderOverrideFileName(): string
    {
        return $this->providerOverrideFileName;
    }

    /*******************************************
     * CALLBACK
     *******************************************/

    /**
     * @return string
     */
    public function getCallbackUrl(): string
    {
        try {
            if ($this->callbackUrlPath === null) {
                return UrlHelper::siteActionUrl(self::DEFAULT_CALLBACK_URL_PATH);
            }

            return UrlHelper::siteUrl($this->callbackUrlPath);
        } catch (Exception $e) {
            if ($this->callbackUrlPath === null) {
                return UrlHelper::actionUrl(self::DEFAULT_CALLBACK_URL_PATH);
            }

            return UrlHelper::url($this->callbackUrlPath);
        }
    }

    /**
     * @param $callbackUrlPath
     * @return $this
     * @throws \yii\base\Exception
     */
    public function setCallbackUrlPath($callbackUrlPath)
    {
        $callbackUrlPath = trim(
            StringHelper::removeLeft(
                (string)$callbackUrlPath,
                UrlHelper::siteUrl()
            ),
            ' /'
        );

        $this->callbackUrlPath = empty($callbackUrlPath) ? null : $callbackUrlPath;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCallbackUrlPath()
    {
        return $this->callbackUrlPath;
    }

    /**
     * @return array|null
     */
    public function getCallbackUrlRule()
    {
        if ($path = $this->callbackUrlPath) {
            return [
                $path => self::DEFAULT_CALLBACK_ROUTE
            ];
        }
        return null;
    }


    /*******************************************
     * PROVIDER SETTINGS VIEW (not currently editable)
     *******************************************/

    /**
     * @return ViewInterface
     */
    public function getProviderSettingsView(): ViewInterface
    {
        return new Template([
            'template' => 'patron/_cp/provider/_settings'
        ]);
    }

    /**
     * @return ViewInterface
     */
    public function getTokenView(): ViewInterface
    {
        return new Template([
            'template' => 'patron/_modal/token'
        ]);
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
                        'callbackUrlPath'
                    ],
                    UriValidator::class
                ],
                [
                    [
                        'callbackUrlPath'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            [
                'callbackUrlPath',
                'environments',
                'environment'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'callbackUrlPath' => Craft::t('patron', 'Callback Url Path'),
            ]
        );
    }
}
