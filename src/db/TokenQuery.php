<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\db;

use craft\db\Query;
use craft\db\QueryAbortedException;
use flipbox\ember\db\traits\AuditAttributes;
use flipbox\ember\db\traits\FixedOrderBy;
use flipbox\patron\Patron;
use flipbox\patron\records\Token;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class TokenQuery extends Query
{
    use traits\TokenAttributes,
        FixedOrderBy,
        AuditAttributes;

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        $this->orderBy = [Token::tableAlias() . '.dateCreated' => SORT_DESC];
        $this->from = [Token::tableName() . ' ' . Token::tableAlias()];
        $this->select = [Token::tableAlias() . '.*'];

        parent::__construct($config);
    }

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
