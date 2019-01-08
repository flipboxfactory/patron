<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/patron/domains/
 */

namespace flipbox\patron\queries;

use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderInstance;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait ProviderAttributesTrait
{
    use ProviderClientAttributesTrait,
        ProviderEnvironmentAttributesTrait;

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
     * @var string|string[]|null The provider classes(s). Prefix IDs with "not " to exclude them.
     */
    public $class;

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
     * @param $class
     * @return $this
     */
    public function class($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @throws QueryAbortedException
     */
    protected function applyProviderConditions()
    {
        // Is the query already doomed?
        if ($this->id !== null && empty($this->id)) {
            throw new QueryAbortedException();
        }

        if ($this->enabled !== null) {
            $this->andWhere(Db::parseParam('enabled', $this->enabled));
        }

        $attributes = ['id', 'name', 'handle', 'class'];

        foreach ($attributes as $attribute) {
            if (($value = $this->{$attribute}) !== null) {
                $this->andWhere(Db::parseParam(Provider::tableAlias() . '.' . $attribute, $value));
            }
        }

        $this->applyClientConditions();
        $this->applyInstanceConditions();
        $this->applyEnvironmentConditions();
    }

    /*******************************************
     * PARAMS
     *******************************************/

    /**
     * Apply environment params
     */
    protected function applyInstanceConditions()
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
}
