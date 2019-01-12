<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\queries;

use flipbox\craft\ember\queries\ActiveQuery;
use flipbox\craft\ember\queries\AuditAttributesTrait;
use flipbox\craft\ember\queries\UserAttributeTrait;
use flipbox\patron\Patron;
use flipbox\patron\records\Token;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class TokenActiveQuery extends ActiveQuery
{
    use TokenAttributesTrait,
        TokenProviderAttributeTrait,
        TokenEnvironmentAttributeTrait,
        UserAttributeTrait,
        AuditAttributesTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->orderBy = [
            Token::tableAlias() . '.enabled' => SORT_DESC,
            Token::tableAlias() . '.dateExpires' => SORT_DESC,
            Token::tableAlias() . '.dateUpdated' => SORT_DESC
        ];

        $this->from = [Token::tableName() . ' ' . Token::tableAlias()];

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
     * @throws \ReflectionException
     */
    public function prepare($builder)
    {
        $this->applyTokenConditions();
        $this->applyProviderConditions();
        $this->applyEnvironmentConditions();
        $this->applyAuditAttributeConditions();

        return parent::prepare($builder);
    }
}
