<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\queries;

use craft\db\QueryAbortedException;
use flipbox\craft\ember\queries\ActiveQuery;
use flipbox\craft\ember\queries\AuditAttributesTrait;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ProviderActiveQuery extends ActiveQuery
{
    use ProviderAttributesTrait,
        AuditAttributesTrait;

    /**
     * @inheritdoc
     */
    public $orderBy = [
        'enabled' => SORT_DESC,
        'dateUpdated' => SORT_DESC
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->from = [Provider::tableName() . ' ' . Provider::tableAlias()];

        parent::init();

        if ($this->environment === null) {
            $this->environment = Patron::getInstance()->getSettings()->getEnvironment();
        }
    }

    /*******************************************
     * PREPARE
     *******************************************/

    /**
     * @inheritdoc
     *
     * @throws QueryAbortedException if it can be determined that there won’t be any results
     */
    public function prepare($builder)
    {
        $this->applyProviderConditions();
        $this->applyAuditAttributeConditions();

        return parent::prepare($builder);
    }
}
