<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/patron/domains/
 */

namespace flipbox\patron\queries;

use craft\helpers\Db;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\patron\records\Provider as ProviderRecord;
use flipbox\patron\records\Token;
use League\OAuth2\Client\Provider\AbstractProvider;
use yii\db\Query;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait TokenProviderAttributeTrait
{
    /**
     * The provider(s) that the resulting must have.
     *
     * @var string|string[]|int|int[]|ProviderRecord|ProviderRecord[]|AbstractProvider|AbstractProvider[]|null
     */
    public $provider;

    /**
     * @param string|string[]|int|int[]|ProviderRecord|ProviderRecord[]|AbstractProvider|AbstractProvider[]|null $value
     * @return static The query object
     */
    public function setProvider($value)
    {
        $this->provider = $value;
        return $this;
    }

    /**
     * @param string|string[]|int|int[]|ProviderRecord|ProviderRecord[]|AbstractProvider|AbstractProvider[]|null $value
     * @return static The query object
     */
    public function provider($value)
    {
        return $this->setProvider($value);
    }

    /**
     * @param string|string[]|int|int[]|ProviderRecord|ProviderRecord[]|AbstractProvider|AbstractProvider[]|null $value
     * @return static The query object
     */
    public function setProviderId($value)
    {
        return $this->setProvider($value);
    }

    /**
     * @param string|string[]|int|int[]|ProviderRecord|ProviderRecord[]|AbstractProvider|AbstractProvider[]|null $value
     * @return static The query object
     */
    public function providerId($value)
    {
        return $this->setProvider($value);
    }

    /**
     * @param $value
     * @return array|string
     */
    protected function parseProviderValue($value)
    {
        return QueryHelper::prepareParam(
            $value,
            function (string $identifier) {
                $value = (new Query())
                    ->select(['id'])
                    ->from([ProviderRecord::tableName()])
                    ->where(['handle' => $identifier])
                    ->scalar();
                return empty($value) ? false : $value;
            }
        );
    }

    /**
     * @throws \ReflectionException
     */
    protected function applyProviderConditions()
    {
        if (empty($this->provider)) {
            return;
        }

        $this->andWhere(
            Db::parseParam(Token::tableAlias() . '.providerId', $this->parseProviderValue($this->provider))
        );
    }
}
