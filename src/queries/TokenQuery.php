<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\queries;

use craft\db\Query;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use flipbox\craft\ember\queries\AuditAttributesTrait;
use flipbox\craft\ember\queries\PopulateObjectTrait;
use flipbox\patron\records\Token;
use League\OAuth2\Client\Token\AccessToken;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class TokenQuery extends Query
{
    use TokenAttributesTrait,
        TokenProviderAttributeTrait,
        AuditAttributesTrait,
        PopulateObjectTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->orderBy = [
            Token::tableAlias() . '.enabled' => SORT_DESC,
            Token::tableAlias() . '.dateExpires' => SORT_DESC,
            Token::tableAlias() . '.dateUpdated' => SORT_DESC
        ];
        $this->from = [Token::tableName() . ' ' . Token::tableAlias()];
        $this->select = [Token::tableAlias() . '.*'];

        parent::init();
    }


    /*******************************************
     * RESULTS
     *******************************************/

    /**
     * @inheritdoc
     * @throws \Exception
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
     * @return AccessToken
     * @throws \Exception
     */
    protected function createObject($config)
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

        $config = array_merge($config, (array)$values);

        return new AccessToken($config);
    }

    /**
     * @param \DateTime|null $dateTime
     * @return int|null
     */
    private function calculateExpires(\DateTime $dateTime = null)
    {
        return $dateTime ? ($dateTime->getTimestamp() - DateTimeHelper::currentUTCDateTime()->getTimestamp()) : null;
    }


    /*******************************************
     * PREPARE
     *******************************************/

    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public function prepare($builder)
    {
        $this->applyTokenConditions();
        $this->applyProviderConditions();
        $this->applyAuditAttributeConditions();

        return parent::prepare($builder);
    }
}
