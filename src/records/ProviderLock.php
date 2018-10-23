<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\records\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $providerId
 * @property int $pluginId
 */
class ProviderLock extends ActiveRecord
{
    use traits\ProviderAttribute;

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'patron_provider_locks';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'pluginId'
                    ],
                    'number',
                    'integerOnly' => true
                ],
                [
                    [
                        'providerId',
                        'pluginId'
                    ],
                    'required'
                ],
                [
                    [
                        'pluginId'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }
}
