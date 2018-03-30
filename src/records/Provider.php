<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use Craft;
use craft\helpers\Template;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\records\ActiveRecordWithId;
use flipbox\ember\records\traits\StateAttribute;
use flipbox\ember\traits\HandleRules;
use flipbox\patron\db\ProviderActiveQuery;
use flipbox\patron\helpers\ProviderHelper;
use flipbox\patron\Patron;
use flipbox\patron\providers\SettingsInterface;
use flipbox\patron\validators\ProviderValidator;
use Twig_Markup;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property string $clientId
 * @property string $clientSecret
 * @property string $class
 * @property array $settings
 * @property Token[] tokens
 */
class Provider extends ActiveRecordWithId
{
    use HandleRules,
        StateAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'patron_providers';

    /**
     * @var SettingsInterface
     */
    private $settingsModel;

    /**
     * The length of the identifier
     */
    const CLIENT_ID_LENGTH = 100;

    /**
     * The length of the secret
     */
    const CLIENT_SECRET_LENGTH = 100;

    /**
     * @inheritdoc
     * @return ProviderActiveQuery
     */
    public static function find()
    {
        return Craft::createObject(ProviderActiveQuery::class, [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->handleRules(),
            $this->stateRules(),
            [
                [
                    [
                        'clientId'
                    ],
                    'string',
                    'max' => static::CLIENT_ID_LENGTH
                ],
                [
                    [
                        'clientSecret'
                    ],
                    'string',
                    'max' => static::CLIENT_SECRET_LENGTH
                ],
                [
                    [
                        'class'
                    ],
                    ProviderValidator::class
                ],
                [
                    [
                        'clientId',
                        'class'
                    ],
                    'required'
                ],
                [
                    [
                        'clientId',
                        'clientSecret',
                        'class',
                        'settings'
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
     * Get all of the tokens that belong to the authorization.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTokens()
    {
        return $this->hasMany(
            Token::class,
            ['providerId' => 'id']
        );
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return ProviderHelper::displayName(
            $this->class
        );
    }

    /**
     * @return Twig_Markup
     * @throws \yii\base\InvalidConfigException
     */
    public function getSettingsHtml(): Twig_Markup
    {
        return Template::raw(
            $this->getSettingsModel()->inputHtml()
        );
    }

    /**
     * @return SettingsInterface
     * @throws \yii\base\InvalidConfigException
     */
    protected function getSettingsModel(): SettingsInterface
    {
        if (!$this->settingsModel instanceof SettingsInterface) {
            $this->settingsModel = Patron::getInstance()->manageProviders()->resolveSettings($this, $this->settings);
        }

        return $this->settingsModel;
    }
}
