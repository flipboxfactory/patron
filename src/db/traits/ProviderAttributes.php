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
use flipbox\patron\records\ProviderInstance;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait ProviderAttributes
{
    use BaseProviderAttributes;

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

        $this->applyProviderParam();
        $this->applySettingsParam();
        $this->applyEnvironmentParams();
    }

    /*******************************************
     * PARAMS
     *******************************************/

    /**
     * Apply environment params
     */
    protected function applyProviderParam()
    {
        $attributes = ['id', 'name', 'handle'];

        foreach ($attributes as $attribute) {
            if (($value = $this->{$attribute}) !== null) {
                $this->andWhere(Db::parseParam(Provider::tableAlias() . '.' . $attribute, $value));
            }
        }
    }

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
}
