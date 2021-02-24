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
use flipbox\craft\ember\queries\PopulateObjectTrait;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
use League\OAuth2\Client\Provider\AbstractProvider;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ProviderQuery extends Query
{
    use ProviderAttributesTrait,
        AuditAttributesTrait,
        PopulateObjectTrait;

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
            Provider::tableAlias() . '.*'
        ];

        parent::init();
    }


    /*******************************************
     * RESULTS
     *******************************************/

    /**
     * @inheritdoc
     * @return array|mixed|null
     * @throws \yii\base\InvalidConfigException
     */
    public function one($db = null)
    {
        if (null === ($config = parent::one($db))) {
            return null;
        }

        return $this->createObject($config);
    }

    /*******************************************
     * CREATE OBJECT
     *******************************************/

    /**
     * @param array $config
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    protected function createObject($config)
    {
        $config = $this->prepareConfig($config);

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
    protected function prepareConfig(array $config = []): array
    {
        // Merge in settings
        $config = array_merge($config, $this->extractSettings($config));

        // Apply override settings
        if (null !== ($handle = $config['handle'] ?? null)) {
            $config = array_merge(
                $config,
                Patron::getInstance()->getSettings()->getProvider($handle)
            );
        }

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
