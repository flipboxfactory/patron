<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\services;

use flipbox\ember\helpers\ArrayHelper;
use flipbox\ember\services\traits\records\Accessor;
use flipbox\patron\db\TokenQuery;
use flipbox\patron\Patron;
use flipbox\patron\records\Token;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use yii\base\Component;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method Token create(array $attributes = [])
 * @method TokenQuery getQuery($config = [])
 * @method Token parentFind($identifier)
 * @method Token get($identifier)
 * @method Token findByCondition($condition = [])
 * @method Token getByCondition($condition = [])
 * @method Token findByCriteria($criteria = [])
 * @method Token getByCriteria($criteria = [])
 * @method Token[] findAllByCondition($condition = [])
 * @method Token[] getAllByCondition($condition = [])
 * @method Token[] findAllByCriteria($criteria = [])
 * @method Token[] getAllByCriteria($criteria = [])
 */
class ManageTokens extends Component
{
    use Accessor {
        find as parentFind;
    }

    /**
     * @inheritdoc
     */
    public static function recordClass(): string
    {
        return Token::class;
    }

    /**
     * @param array $config
     * @return array
     */
    protected function prepareQueryConfig($config = [])
    {
        if (!is_array($config)) {
            $config = ArrayHelper::toArray($config, [], true);
        }

        // Allow disabled when managing
        if (!array_key_exists('enabled', $config)) {
            $config['enabled'] = null;
        }

        // Allow all environments when managing
        if (!array_key_exists('environment', $config)) {
            $config['environment'] = null;
        }

        return $config;
    }

    /**
     * @inheritdoc
     */
    public function find($identifier)
    {
        if ($identifier instanceof AccessToken) {
            return $this->findByAccessToken($identifier);
        }

        if ($identifier instanceof AbstractProvider) {
            return $this->findByProvider($identifier);
        }

        return $this->parentFind($identifier);
    }

    /*******************************************
     * FIND/GET BY ACCESS TOKEN
     *******************************************/

    /**
     * @param AccessToken $accessToken
     * @return Token|null
     */
    public function findByAccessToken(AccessToken $accessToken)
    {
        return $this->findByCriteria(['accessToken' => $accessToken->getToken()]);
    }

    /*******************************************
     * FIND/GET BY PROVIDER
     *******************************************/

    /**
     * @param AbstractProvider $provider
     * @return Token|null
     */
    public function findByProvider(AbstractProvider $provider)
    {
        if (null === ($providerId = Patron::getInstance()->getProviders()->getId($provider))) {
            return null;
        }

        return $this->findByCriteria(['providerId' => $providerId]);
    }

    /*******************************************
     * STATES
     *******************************************/

    /**
     * @param Token $record
     * @return bool
     */
    public function disable(Token $record)
    {
        $record->enabled = false;
        return $record->save(true, ['enabled']);
    }

    /**
     * @param Token $model
     * @return bool
     */
    public function enable(Token $model)
    {
        $model->enabled = true;
        return $model->save(true, ['enabled']);
    }
}
