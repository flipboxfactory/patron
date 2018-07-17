<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\db;

use craft\db\QueryAbortedException;
use flipbox\ember\db\traits\AuditAttributes;
use flipbox\ember\db\traits\FixedOrderBy;
use flipbox\patron\Patron;
use yii\db\ActiveQuery;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class TokenActiveQuery extends ActiveQuery
{
    use traits\TokenAttributes,
        FixedOrderBy,
        AuditAttributes;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->environment === null) {
            $this->environment = Patron::getInstance()->getSettings()->getEnvironment();
        }
    }

    /**
     * @inheritdoc
     */
    public $orderBy = ['dateExpires' => SORT_DESC];

    /*******************************************
     * FIXED ORDER
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function fixedOrderColumn(): string
    {
        return 'accessToken';
    }

    /**
     * @inheritdoc
     *
     * @throws QueryAbortedException if it can be determined that there won’t be any results
     */
    public function prepare($builder)
    {
        $this->applyConditions();
        $this->applyAuditAttributeConditions();
        $this->applyOrderByParams($builder->db);

        return parent::prepare($builder);
    }
}
