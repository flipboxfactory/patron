<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/patron/domains/
 */

namespace flipbox\patron\db\traits;

use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderEnvironment;
use flipbox\patron\records\ProviderInstance;
use yii\db\Expression;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait ProviderAttributes
{
    /**
     * @var bool|null The enabled state
     */
    public $enabled = true;

    /**
     * @var int|int[]|false|null The model ID(s). Prefix IDs with "not " to exclude them.
     */
    public $id;

    /**
     * @var string|string[]|null The provider name(s). Prefix IDs with "not " to exclude them.
     */
    public $name;

    /**
     * @var string|string[]|null The provider handle(s). Prefix IDs with "not " to exclude them.
     */
    public $handle;

    /**
     * @var string|string[]|null The client Id(s). Prefix IDs with "not " to exclude them.
     */
    public $clientId;

    /**
     * @var string|string[]|null The client secret(s). Prefix IDs with "not " to exclude them.
     */
    public $clientSecret;

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
     * Sets the value indicating whether to SELECT DISTINCT or not.
     * @param bool $value whether to SELECT DISTINCT or not.
     * @return $this the query object itself
     */
    abstract public function distinct($value = true);

    /**
     * @param $enabled
     * @return $this
     */
    public function enabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function id($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param $handle
     * @return $this
     */
    public function handle($handle)
    {
        $this->handle = $handle;
        return $this;
    }

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
     * @param $environment
     * @return $this
     */
    public function environment($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @param $settings
     * @return $this
     */
    public function settings($settings)
    {
        $this->$settings = $settings;
        return $this;
    }

    /**
     * @throws QueryAbortedException
     */
    protected function applyConditions()
    {
        // Is the query already doomed?
        if ($this->id !== null && empty($this->id)) {
            throw new QueryAbortedException();
        }

        if ($this->enabled !== null) {
            $this->andWhere(Db::parseParam('enabled', $this->enabled));
        }

        $attributes = ['id', 'name', 'handle'];

        foreach ($attributes as $attribute) {
            if (($value = $this->{$attribute}) !== null) {
                $this->andWhere(Db::parseParam(Provider::tableAlias() . '.' . $attribute, $value));
            }
        }

        $this->applySettingsParam();
        $this->applyEnvironmentParam();
    }

    /*******************************************
     * PARAMS
     *******************************************/

    /**
     * Apply environment params
     */
    protected function applySettingsParam()
    {
        $alias = ProviderInstance::tableAlias();

        $this->leftJoin(
            ProviderInstance::tableName() . ' ' . $alias,
            '[[' . $alias . '.providerId]] = [[' . Provider::tableAlias() . '.id]]'
        );

        $attributes = ['clientId', 'clientSecret'];

        foreach ($attributes as $attribute) {
            if (null !== ($value = $this->{$attribute})) {
                $this->andWhere(Db::parseParam($alias . '.' . $attribute, $value));
            }
        }

        $this->distinct(true);
    }

    /**
     * Apply environment params
     */
    protected function applyEnvironmentParam()
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
