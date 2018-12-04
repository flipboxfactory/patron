<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/patron/domains/
 */

namespace flipbox\patron\queries;

use craft\helpers\Db;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\patron\helpers\ProviderHelper;
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
     * @param string $join
     * @return array
     */
    protected function parseProviderValue($value, string $join = 'or'): array
    {
        if (false === QueryHelper::parseBaseParam($value, $join)) {
            foreach ($value as $operator => &$v) {
                $this->resolveProviderValue($operator, $v);
            }
        }

        // Filter null and empties
        $value = array_filter($value, function ($arr): bool {
            return $arr !== null && $arr !== '';
        });

        if (empty($value)) {
            return [];
        }

        return array_merge([$join], $value);
    }

    /**
     * @param $operator
     * @param $value
     */
    protected function resolveProviderValue($operator, &$value)
    {
        if ($value instanceof AbstractProvider) {
            $value = ProviderHelper::lookupId($value);
        }

        if (false === QueryHelper::findParamValue($value, $operator)) {
            if (is_string($value)) {
                $value = $this->resolveProviderStringValue($value);
            }

            if ($value instanceof ProviderRecord) {
                $value = $value->id;
            }

            if ($value) {
                $value = QueryHelper::assembleParamValue($value, $operator);
            }
        }
    }

    /**
     * @param string $value
     * @return int|null
     */
    protected function resolveProviderStringValue(string $value)
    {
        if (is_numeric($value)) {
            $condition = ['id' => $value];
        } else {
            $condition = ['handle' => $value];
        }

        $id = (new Query())
            ->select(['id'])
            ->from([ProviderRecord::tableName()])
            ->where($condition)
            ->scalar();

        return $id ? (int)$id : null;
    }

    /**
     *
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
