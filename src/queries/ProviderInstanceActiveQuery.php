<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\queries;

use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\craft\ember\models\AuditAttributesTrait;
use flipbox\patron\records\ProviderInstance;
use yii\db\ActiveQuery;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ProviderInstanceActiveQuery extends ActiveQuery
{
    use ProviderClientAttributesTrait,
        ProviderEnvironmentAttributesTrait,
        AuditAttributesTrait;

    /**
     * @var int|int[]|false|null The model ID(s). Prefix IDs with "not " to exclude them.
     */
    public $id;

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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->from === null) {
            $this->from = [ProviderInstance::tableName()];
        }
    }


    /**
     * @inheritdoc
     *
     * @throws QueryAbortedException if it can be determined that there won’t be any results
     */
    public function prepare($builder)
    {
        // Is the query already doomed?
        if ($this->id !== null && empty($this->id)) {
            throw new QueryAbortedException();
        }

        $this->applyClientConditions();
        $this->applyProviderParam();
        $this->applyEnvironmentConditions();

        return parent::prepare($builder);
    }

    /**
     * Apply environment params
     */
    protected function applyProviderParam()
    {
        $attributes = ['id'];

        foreach ($attributes as $attribute) {
            if (null !== ($value = $this->{$attribute})) {
                $this->andWhere(Db::parseParam(ProviderInstance::tableAlias() . '.' . $attribute, $value));
            }
        }

        $this->distinct(true);
    }
}
