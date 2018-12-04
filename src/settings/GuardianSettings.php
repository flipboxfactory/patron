<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\settings;

use Craft;
use craft\helpers\Json;
use craft\validators\UrlValidator;
use flipbox\craft\ember\helpers\ModelHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class GuardianSettings extends BaseSettings
{
    /**
     * The input scope delimiter
     */
    const SCOPE_DELIMITER = ' ';

    /**
     * @var string
     */
    public $baseAuthorizationUrl;

    /**
     * @var string
     */
    public $baseAccessTokenUrl;

    /**
     * @var string
     */
    public $baseResourceOwnerDetailsUrl;

    /**
     * @var array
     */
    private $defaultScopes = [];

    /**
     * @return array
     */
    public function getDefaultScopes(): array
    {
        return $this->defaultScopes;
    }

    /**
     * @param null|string|string[] $scopes
     * @return $this
     */
    public function setDefaultScopes($scopes = null)
    {
        $this->defaultScopes = [];

        $scopes = $this->normalizeScopes($scopes);

        if (!empty($scopes)) {
            $this->defaultScopes = $scopes;
        }

        return $this;
    }

    /**
     * @param mixed $scopes
     * @return array
     */
    private function normalizeScopes($scopes = null): array
    {
        if (is_string($scopes)) {
            $scopes = Json::decodeIfJson($scopes);

            // Not json ... it's just a string
            if (is_string($scopes)) {
                $scopes = explode(
                    static::SCOPE_DELIMITER,
                    $scopes
                );
            }
        }

        if (!is_array($scopes)) {
            $scopes = [$scopes];
        }

        return array_filter($scopes);
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function inputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate(
            'patron/_settings/guardian',
            [
                'settings' => $this
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
                'defaultScopes'
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
                'baseAuthorizationUrl' => Craft::t('patron', "Authorization Url"),
                'baseAccessTokenUrl' => Craft::t('patron', "Access Token Url"),
                'baseResourceOwnerDetailsUrl' => Craft::t('patron', "Resource Owner Details Url"),
                'defaultScopes' => Craft::t('patron', "Default Scopes")
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
                        'baseAuthorizationUrl',
                        'baseAccessTokenUrl',
                        'baseResourceOwnerDetailsUrl',
                        'defaultScopes'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ],
                [
                    [
                        'baseAuthorizationUrl',
                        'baseAccessTokenUrl',
                        'baseResourceOwnerDetailsUrl'
                    ],
                    'required'
                ],
                [
                    [
                        'baseAuthorizationUrl',
                        'baseAccessTokenUrl',
                        'baseResourceOwnerDetailsUrl'
                    ],
                    UrlValidator::class
                ]
            ]
        );
    }
}
