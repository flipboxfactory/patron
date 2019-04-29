<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/patron/domains/
 */

namespace flipbox\patron\queries;

use craft\helpers\Db;
use flipbox\patron\records\Provider;
use yii\db\Expression;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait ProviderClientAttributesTrait
{
    /**
     * @var string|string[]|null The client Id(s). Prefix IDs with "not " to exclude them.
     */
    public $clientId;

    /**
     * @var string|string[]|null The client secret(s). Prefix IDs with "not " to exclude them.
     */
    public $clientSecret;

    /**
     * Adds an additional WHERE condition to the existing one.
     * The new condition and the existing one will be joined using the `AND` operator.
     * @param string|array|Expression $condition the new WHERE condition. Please refer to [[where()]]
     * on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return $this the query object itself
     * @see where()
     * @see orWhere()
     */
    abstract public function andWhere($condition, $params = []);

    /**
     * @param $clientId
     * @return $this
     */
    public function clientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @param $clientSecret
     * @return $this
     */
    public function clientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * Apply params
     */
    protected function applyClientConditions()
    {
        $attributes = ['clientId', 'clientSecret'];

        foreach ($attributes as $attribute) {
            if (($value = $this->{$attribute}) !== null) {
                $this->andWhere(
                    Db::parseParam(Provider::tableAlias() . '.' . $attribute, $value)
                );
            }
        }
    }
}
