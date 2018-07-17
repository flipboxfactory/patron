<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\services;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use flipbox\ember\services\traits\objects\Accessor;
use flipbox\patron\db\TokenQuery;
use flipbox\patron\events\PersistTokenEvent;
use flipbox\patron\Patron;
use flipbox\patron\records\Token;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use yii\base\Component;
use yii\base\Event;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method AccessToken parentFind($identifier)
 * @method AccessToken get($identifier)
 * @method AccessToken findByCondition($condition = [])
 * @method AccessToken getByCondition($condition = [])
 * @method AccessToken findByCriteria($criteria = [])
 * @method AccessToken getByCriteria($criteria = [])
 * @method AccessToken[] findAllByCondition($condition = [])
 * @method AccessToken[] getAllByCondition($condition = [])
 * @method AccessToken[] findAllByCriteria($criteria = [])
 * @method AccessToken[] getAllByCriteria($criteria = [])
 */
class Tokens extends Component
{
    use Accessor {
        find as parentFind;
    }

    /**
     * The before persist token event name
     */
    const EVENT_BEFORE_PERSIST_TOKEN = 'beforePersistToken';

    /**
     *  The after persist token event name
     */
    const EVENT_AFTER_PERSIST_TOKEN = 'afterPersistToken';

    /**
     * @inheritdoc
     */
    public static function objectClass()
    {
        return AccessToken::class;
    }

    /**
     * @inheritdoc
     */
    public static function objectClassInstance()
    {
        return AccessToken::class;
    }

    /**
     * @inheritdoc
     * @return TokenQuery
     */
    public function getQuery($config = []): QueryInterface
    {
        $query = new TokenQuery();

        if (!empty($config)) {
            Craft::configure(
                $query,
                $config
            );
        }

        return $query;
    }

    /**
     * @param AccessToken $accessToken
     * @param AbstractProvider $provider
     * @return bool
     * @throws \Exception
     */
    public function persistNewToken(
        AccessToken $accessToken,
        AbstractProvider $provider
    ): bool {

        $config = [
            'accessToken' => $accessToken->getToken(),
            'refreshToken' => $accessToken->getRefreshToken(),
            'providerId' => Patron::getInstance()->getProviders()->getId($provider),
            'values' => $accessToken->getValues(),
            'dateExpires' => $accessToken->getExpires(),
            'enabled' => true
        ];

        $record = Patron::getInstance()->manageTokens()->create($config);

        $event = new PersistTokenEvent([
            'token' => $accessToken,
            'provider' => $provider,
            'record' => $record
        ]);

        Event::trigger(
            get_class($provider),
            self::EVENT_BEFORE_PERSIST_TOKEN,
            $event
        );

        if (!$record->save()) {
            return false;
        }

        Event::trigger(
            get_class($provider),
            self::EVENT_AFTER_PERSIST_TOKEN,
            $event
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function find($identifier)
    {
        if ($identifier instanceof AbstractProvider) {
            return $this->findByProvider($identifier);
        }

        return $this->parentFind($identifier);
    }

    /*******************************************
     * FIND/GET BY PROVIDER
     *******************************************/

    /**
     * @param AbstractProvider $provider
     * @return AccessToken|null
     */
    public function findByProvider(AbstractProvider $provider)
    {
        if (null === ($providerId = Patron::getInstance()->getProviders()->getId($provider))) {
            return null;
        }

        return $this->findByCriteria(['providerId' => $providerId]);
    }


    /*******************************************
     * CREATE
     *******************************************/

    /**
     * @param array $config
     * @return AccessToken
     */
    public function create($config = []): AccessToken
    {
        if ($config instanceof Token) {
            $config = $this->prepareConfigFromRecord($config);
        }

        if (!is_array($config)) {
            $config = ArrayHelper::toArray($config, [], false);
        }

        $class = static::objectClass();

        return new $class(
            $this->prepareConfig($config)
        );
    }

    /**
     * @param array $config
     * @return array
     */
    protected function prepareConfig(array $config = []): array
    {
        $config['revoked'] = !(bool)ArrayHelper::remove($config, 'enabled', true);
        $config['access_token'] = ArrayHelper::remove($config, 'accessToken');
        $config['refresh_token'] = ArrayHelper::remove($config, 'refreshToken');
        $config['resource_owner_id'] = ArrayHelper::remove($config, 'userId');

        // Handle DateTime expires
        if (false !== ($dateTime = DateTimeHelper::toDateTime(ArrayHelper::remove($config, 'dateExpires')))) {
            $config['expires'] = $this->calculateExpires($dateTime);
        }

        $values = ArrayHelper::remove($config, 'values', []);
        if (is_string($values)) {
            $values = Json::decodeIfJson($values);
        }

        return array_merge($config, $values);
    }

    /**
     * @param \DateTime|null $dateTime
     * @return int|null
     */
    private function calculateExpires(\DateTime $dateTime = null)
    {
        return $dateTime ? ($dateTime->getTimestamp() - DateTimeHelper::currentUTCDateTime()->getTimestamp()) : null;
    }
}
