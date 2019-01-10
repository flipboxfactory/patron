<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/patron/domains/
 */

namespace flipbox\patron\queries;

use craft\helpers\Db;
use flipbox\patron\records\ProviderEnvironment;
use flipbox\patron\records\ProviderInstance;
use yii\db\Expression;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait ProviderEnvironmentAttributesTrait
{
    /**
     * @var string|string[]|null The environment(s). Prefix with "not " to exclude them.
     */
    public $environment;

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
     * Appends a LEFT OUTER JOIN part to the query.
     * @param string|array $table the table to be joined.
     *
     * Use a string to represent the name of the table to be joined.
     * The table name can contain a schema prefix (e.g. 'public.user') and/or table alias (e.g. 'user u').
     * The method will automatically quote the table name unless it contains some parenthesis
     * (which means the table is given as a sub-query or DB expression).
     *
     * Use an array to represent joining with a sub-query. The array must contain only one element.
     * The value must be a [[Query]] object representing the sub-query while the corresponding key
     * represents the alias for the sub-query.
     *
     * @param string|array $on the join condition that should appear in the ON part.
     * Please refer to [[join()]] on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query
     * @return $this the query object itself
     */
    abstract public function leftJoin($table, $on = '', $params = []);

    /**
     * @param $environment
     * @return $this
     */
    public function environment($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * Apply environment params
     */
    protected function applyEnvironmentConditions()
    {
        if (empty($this->environment)) {
            return;
        }

        $alias = ProviderEnvironment::tableAlias();

        $this->leftJoin(
            ProviderEnvironment::tableName() . ' ' . $alias,
            '[[' . $alias . '.instanceId]] = [[' . ProviderInstance::tableAlias() . '.id]]'
        );

        $this->andWhere(
            Db::parseParam($alias . '.environment', $this->environment)
        );
    }
}
