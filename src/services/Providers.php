<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\services;

use Craft;
use craft\db\Query;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\ember\helpers\ObjectHelper;
use flipbox\ember\services\traits\objects\AccessorByString;
use flipbox\patron\db\ProviderQuery;
use flipbox\patron\helpers\ProviderHelper;
use flipbox\patron\Patron;
use flipbox\patron\providers\SettingsInterface;
use flipbox\patron\records\Provider;
use flipbox\patron\records\Provider as ProviderRecord;
use flipbox\patron\records\ProviderInstance;
use League\OAuth2\Client\Provider\AbstractProvider;
use yii\base\Component;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method AbstractProvider find($identifier)
 * @method AbstractProvider get($identifier)
 * @method AbstractProvider findByString($identifier)
 * @method AbstractProvider getByString($identifier)
 * @method AbstractProvider findByCondition($condition = [])
 * @method AbstractProvider getByCondition($condition = [])
 * @method AbstractProvider findByCriteria($criteria = [])
 * @method AbstractProvider getByCriteria($criteria = [])
 * @method AbstractProvider[] findAllByCondition($condition = [])
 * @method AbstractProvider[] getAllByCondition($condition = [])
 * @method AbstractProvider[] findAllByCriteria($criteria = [])
 * @method AbstractProvider[] getAllByCriteria($criteria = [])
 */
class Providers extends Component
{
    use AccessorByString {
        prepareConfig as parentPrepareConfig;
        buildQueryFromCondition as parentBuildQueryFromCondition;
    }

    /**
     * @param array $condition
     * @return QueryInterface
     */
    protected function buildQueryFromCondition($condition = []): QueryInterface
    {
        if (is_numeric($condition)) {
            $condition = ['id' => $condition];
        }

        return $this->parentBuildQueryFromCondition($condition);
    }

    /**
     * @inheritdoc
     */
    public static function stringProperty(): string
    {
        return 'handle';
    }

    /**
     * @inheritdoc
     */
    public static function objectClassInstance()
    {
        return AbstractProvider::class;
    }

    /**
     * @inheritdoc
     */
    public static function objectClass()
    {
        return null;
    }

    /*******************************************
     * GET ID
     *******************************************/

    /**
     * @param AbstractProvider $provider
     * @return int|null
     */
    public function getId(AbstractProvider $provider)
    {
        list($clientId, $clientSecret) = ProviderHelper::getProtectedProperties(
            $provider,
            ['clientId', 'clientSecret']
        );

        if (Patron::getInstance()->getSettings()->encryptStorageData === true) {
            return $this->getIdFromEncryptedClientSecret(
                get_class($provider),
                $clientId,
                $clientSecret
            );
        }

        return $this->getIdFromUnencryptedClientSecret(
            get_class($provider),
            $clientId,
            $clientSecret
        );
    }

    /**
     * @param string $class
     * @param string $clientId
     * @param string $clientSecret
     * @return int|null
     */
    protected function getIdFromEncryptedClientSecret(string $class, string $clientId, string $clientSecret)
    {
        $condition = [
            'class' => $class,
            'clientId' => $clientId,
        ];

        $rows = (new Query())
            ->select(['providerId', 'clientSecret'])
            ->from([ProviderInstance::tableName() . ' ' . ProviderInstance::tableAlias()])
            ->leftJoin(
                [ProviderRecord::tableName() . ' ' . ProviderRecord::tableAlias()],
                ProviderRecord::tableAlias() . '.id=providerId'
            )
            ->where($condition)
            ->all();

        foreach ($rows as $row) {
            $secret = $row['clientSecret'] ?? '';

            if ($clientSecret === ProviderHelper::decryptClientSecret($secret)) {
                return (int) $row['providerId'];
            }
        }

        return null;
    }

    /**
     * @param string $class
     * @param string $clientId
     * @param string $clientSecret
     * @return int|null
     */
    protected function getIdFromUnencryptedClientSecret(string $class, string $clientId, string $clientSecret)
    {
        $condition = [
            'class' => $class,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret
        ];

        if (!$providerId = (new Query())
            ->select(['providerId'])
            ->from([ProviderInstance::tableName() . ' ' . ProviderInstance::tableAlias()])
            ->leftJoin(
                [ProviderRecord::tableName() . ' ' . ProviderRecord::tableAlias()],
                ProviderRecord::tableAlias() . '.id=providerId'
            )
            ->where($condition)
            ->scalar()
        ) {
            return null;
        }

        return (int)$providerId;
    }


    /*******************************************
     * QUERY
     *******************************************/

    /**
     * @param array $config
     * @return ProviderQuery
     */
    public function getQuery(array $config = []): ProviderQuery
    {
        $query = new ProviderQuery();

        if (!empty($config)) {
            Craft::configure(
                $query,
                $config
            );
        }

        return $query;
    }


    /**
     * @param array $config
     * @return AbstractProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function create($config = [])
    {
        if ($config instanceof Provider) {
            $config = $this->prepareConfigFromRecord($config);
        }

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
    protected function prepareConfig($config = []): array
    {
        $config = $this->parentPrepareConfig($config);

        // Merge in settings
        $config = array_merge($config, $this->mergeSettings($config));

        // This doesn't change
        $config['redirectUri'] = Patron::getInstance()->getSettings()->getCallbackUrl();

//        // Overrides?
//        $config = $this->mergeOverrides($config);

        return $config;
    }

    /**
     * @param array $config
     * @return array
     */
    protected function mergeSettings(array &$config): array
    {
        // We could init the SettingsInterface and pass them through there if needed
        $settings = ArrayHelper::remove($config, 'settings', []);

        if ($settings instanceof SettingsInterface) {
            return $settings->toConfig();
        }

        if (is_string($settings)) {
            $settings = Json::decodeIfJson($settings);
        }

        if (!is_array($settings)) {
            $settings = [$settings];
        }

        return $settings;
    }
}
