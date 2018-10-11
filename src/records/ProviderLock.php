<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\ember\records\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $providerId
 * @property int $pluginId
 */
class ProviderLock extends ActiveRecord
{
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
                        'providerId',
                        'pluginId'
                    ],
                    'number',
                    'integerOnly' => true
                ],
                [
                    [
                        'tokenId',
                        'pluginId'
                    ],
                    'required'
                ],
                [
                    [
                        'tokenId',
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

    /**
     * Get the associated Authorization
     *
     * @param array $config
     * @return ActiveQueryInterface
     */
    public function getProvider(array $config = [])
    {
        $query = $this->hasOne(
            Provider::class,
            ['providerId' => 'id']
        );

        if (!empty($config)) {
            QueryHelper::configure(
                $query,
                $config
            );
        }

        return $query;
    }
}
