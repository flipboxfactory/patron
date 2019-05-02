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
use flipbox\craft\ember\helpers\UrlHelper;
use flipbox\craft\ember\views\Template;
use flipbox\craft\ember\views\ViewInterface;
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
     * @var array|null
     */
    private $providers = [];


    /*******************************************
     * PROVIDER OVERRIDES
     *******************************************/

    /**
     * Get an array provider override configurations
     *
     * @return array
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Set an array provider override configurations
     *
     * @param array $providers
     * @return static
     */
    public function setProviders(array $providers)
    {
        $this->providers = $providers;
        return $this;
    }

    /**
     * Get a provider override configuration by the provider handle
     *
     * @param string $handle
     * @return array
     */
    public function getProvider(string $handle): array
    {
        return $this->providers[$handle] ?? [];
    }


    /*******************************************
     * ENCRYPTION [DEPRECATED]
     *******************************************/

    /**
     * @param bool $value
     * @return $this
     *
     * @deprecated
     */
    public function setEncryptStorageData(bool $value)
    {
        return $this;
    }

    /*******************************************
     * TOKEN ENVIRONMENTS [DEPRECATED]
     *******************************************/

    /**
     * @param bool $value
     * @return $this
     *
     * @deprecated
     */
    public function setAutoPopulateTokenEnvironments(bool $value)
    {
        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     *
     * @deprecated
     */
    public function setApplyProviderEnvironmentsToToken(bool $value)
    {
        return $this;
    }

    /*******************************************
     * ENVIRONMENTS [DEPRECATED]
     *******************************************/

    /**
     * @param string $environment
     * @return $this
     *
     * @deprecated
     */
    public function setEnvironment(string $environment)
    {
        return $this;
    }

    /**
     * @param array $environments
     * @return $this
     *
     * @deprecated
     */
    public function setEnvironments(array $environments)
    {
        return $this;
    }


    /**
     * @param array $environments
     * @return $this
     *
     * @deprecated
     */
    public function setDefaultEnvironments(array $environments)
    {
        return $this;
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
     * @throws Exception
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
     * @return ViewInterface'
     */
    public function getProviderSettingsView(): ViewInterface
    {
        return new Template([
            'template' => 'patron/_cp/provider/_settings'
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
                        'providers'
                    ],
                    'safe',
                    'on' => [
                        self::SCENARIO_DEFAULT
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
                'providers'
            ]
        );
    }

    /**
     * @inheritdocå
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'callbackUrlPath' => Craft::t('patron', 'Callback Url Path'),
                'providers' => Craft::t('patron', 'Provider Overrides')
            ]
        );
    }
}
