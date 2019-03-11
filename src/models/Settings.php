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
use flipbox\craft\ember\helpers\ModelHelper;
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
     * @var array
     */
    private $environments = [];

    /**
     * @var string
     */
    private $environment = null;

    /**
     * Encrypt data in storage
     *
     * @var bool
     * @deprecated
     */
    private $encryptStorageData = true;

    /**
     * Default environments to apply to new Providers when they're created
     *
     * @var array
     */
    private $defaultEnvironments = [];

    /**
     * Auto populate token enviornments upon creation.
     *
     * @var bool
     */
    private $autoPopulateTokenEnvironments = true;

    /**
     * If [[Settings::$autoPopulateTokenEnvironments]] is true, and this is enabled, the environments
     * will mirror the provider environments.
     *
     * @var bool
     */
    private $applyProviderEnvironmentsToToken = false;


    /*******************************************
     * ENCRYPTION
     *******************************************/

    /**
     * @return bool
     * @deprecated
     */
    public function getEncryptStorageData(): bool
    {
        return (bool)$this->encryptStorageData;
    }

    /**
     * @param bool $value
     * @return $this
     * @deprecated
     */
    public function setEncryptStorageData(bool $value)
    {
        $this->encryptStorageData = $value;
        return $this;
    }

    /*******************************************
     * TOKEN ENVIORNMENTS
     *******************************************/

    /**
     * @return bool
     */
    public function getAutoPopulateTokenEnvironments(): bool
    {
        return (bool)$this->autoPopulateTokenEnvironments;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setAutoPopulateTokenEnvironments(bool $value)
    {
        $this->autoPopulateTokenEnvironments = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function getApplyProviderEnvironmentsToToken(): bool
    {
        return (bool)$this->applyProviderEnvironmentsToToken;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setApplyProviderEnvironmentsToToken(bool $value)
    {
        $this->applyProviderEnvironmentsToToken = $value;
        return $this;
    }

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

    /**
     * @return array
     */
    public function getDefaultEnvironments(): array
    {
        if (empty($this->defaultEnvironments)) {
            $this->defaultEnvironments[] = Craft::$app->getConfig()->env;
        }

        return array_intersect(
            $this->getEnvironments(),
            $this->defaultEnvironments
        );
    }

    /**
     * @param array $environments
     * @return $this
     */
    public function setDefaultEnvironments(array $environments)
    {
        $this->defaultEnvironments = $environments;
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
    public function getProviderEnvironmentView(): ViewInterface
    {
        return new Template([
            'template' => 'patron/_cp/provider/_environment'
        ]);
    }

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
                        'encryptStorageData',
                        'applyProviderEnvironmentsToToken',
                        'autoPopulateTokenEnvironments'
                    ],
                    'boolean'
                ],
                [
                    [
                        'callbackUrlPath',
                        'defaultEnvironments',
                        'encryptStorageData',
                        'environments',
                        'applyProviderEnvironmentsToToken',
                        'autoPopulateTokenEnvironments',
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
                'encryptStorageData',
                'environments',
                'applyProviderEnvironmentsToToken',
                'autoPopulateTokenEnvironments',
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
                'defaultEnvironments' => Craft::t('patron', 'Default Enviornments'),
                'encryptStorageData' => Craft::t('patron', 'Encrypt Storage Data'),
                'environments' => Craft::t('patron', 'Environments'),
                'autoPopulateTokenEnvironments' => Craft::t('patron', 'Auto Populate Token Environments'),
                'applyProviderEnvironmentsToToken' => Craft::t('patron', 'Apply Provider Environments to Token')
            ]
        );
    }
}
