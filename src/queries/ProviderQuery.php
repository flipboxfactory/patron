<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\queries;

use craft\db\Query;
use craft\db\QueryAbortedException;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\craft\ember\helpers\ObjectHelper;
use flipbox\craft\ember\queries\AuditAttributesTrait;
use flipbox\patron\helpers\ProviderHelper;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderInstance;
use League\OAuth2\Client\Provider\AbstractProvider;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ProviderQuery extends Query
{
    use ProviderAttributesTrait,
        AuditAttributesTrait;

    /**
     * @inheritdoc
     */
    public $orderBy = [
        'enabled' => SORT_DESC,
        'dateUpdated' => SORT_DESC
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->from = [Provider::tableName() . ' ' . Provider::tableAlias()];

        $this->select = [
            Provider::tableAlias() . '.*',
            ProviderInstance::tableAlias() . '.clientId',
            ProviderInstance::tableAlias() . '.clientSecret',
            ProviderInstance::tableAlias() . '.settings'
        ];

        parent::init();

        if ($this->environment === null) {
            $this->environment = Patron::getInstance()->getSettings()->getEnvironment();
        }
    }

    /**
     * @inheritdoc
     */
    public function populate($rows)
    {
        $results = parent::populate($rows);

        if (Patron::getInstance()->getSettings()->getEncryptStorageData() === true) {
            foreach ($results as $key => $result) {
                $results[$key] = $this->createObject($result, false);
            }
        }

        return $results;
    }

    /**
     * @inheritdoc
     * @return AbstractProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function one($db = null)
    {
        if (null === ($config = parent::one($db))) {
            return null;
        }

        return $this->createObject($config);
    }

    /**
     * @param array $config
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    protected function createObject(array $config, bool $checkSettings = true)
    {
        $config = $this->prepareConfig($config, $checkSettings);

        // Provider class
        $class = ObjectHelper::checkConfig(
            $config,
            AbstractProvider::class
        );

        return new $class($config);
    }

    /**
     * @param array $config
     * @return array
     */
    protected function prepareConfig(array $config = [], bool $checkSettings = true): array
    {
        // Extract 'clientSecret'
        $clientSecret = ArrayHelper::remove($config, 'clientSecret');

        if (!empty($clientSecret)) {
            $config['clientSecret'] = ProviderHelper::decryptClientSecret($clientSecret, $checkSettings);
        }

        // Merge in settings
        $config = array_merge($config, $this->extractSettings($config));

        // This doesn't change
        $config['redirectUri'] = Patron::getInstance()->getSettings()->getCallbackUrl();

        return $config;
    }

    /**
     * @param array $config
     * @return array
     */
    protected function extractSettings(array &$config): array
    {
        // We could init the SettingsInterface and pass them through there if needed
        $settings = ArrayHelper::remove($config, 'settings', []);

        if (is_string($settings)) {
            $settings = Json::decodeIfJson($settings);
        }

        if (!is_array($settings)) {
            $settings = [$settings];
        }

        return $settings;
    }

    /*******************************************
     * PREPARE
     *******************************************/

    /**
     * @inheritdoc
     *
     * @throws QueryAbortedException if it can be determined that there won’t be any results
     */
    public function prepare($builder)
    {
        $this->applyProviderConditions();
        $this->applyAuditAttributeConditions();

        return parent::prepare($builder);
    }
}
