<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use flipbox\ember\records\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class TokenEnvironment extends ActiveRecord
{
    use traits\EnvironmentAttribute,
        traits\TokenAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'patron_token_environments';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = [
        'tokenId'
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->environmentRules(),
            $this->tokenRules(),
            [
                [
                    [
                        'tokenId'
                    ],
                    'required'
                ]
            ]
        );
    }
}
