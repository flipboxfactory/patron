<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\services;

use flipbox\ember\helpers\ArrayHelper;
use flipbox\ember\services\traits\records\Accessor;
use flipbox\patron\db\TokenQuery;
use flipbox\patron\records\Token;
use yii\base\Component;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method Token create(array $attributes = [], string $toScenario = null)
 * @method TokenQuery getQuery($config = [])
 * @method Token find($identifier, string $toScenario = null)
 * @method Token get($identifier, string $toScenario = null)
 * @method Token findByCondition($condition = [], string $toScenario = null)
 * @method Token getByCondition($condition = [], string $toScenario = null)
 * @method Token findByCriteria($criteria = [], string $toScenario = null)
 * @method Token getByCriteria($criteria = [], string $toScenario = null)
 * @method Token[] findAllByCondition($condition = [], string $toScenario = null)
 * @method Token[] getAllByCondition($condition = [], string $toScenario = null)
 * @method Token[] findAllByCriteria($criteria = [], string $toScenario = null)
 * @method Token[] getAllByCriteria($criteria = [], string $toScenario = null)
 */
class ManageTokens extends Component
{
    use Accessor;

    /**
     * @inheritdoc
     */
    public static function recordClass(): string
    {
        return Token::class;
    }

    /**
     * @param array $config
     * @return array
     */
    protected function prepareQueryConfig($config = [])
    {
        if (!is_array($config)) {
            $config = ArrayHelper::toArray($config, [], true);
        }

        // Allow disabled when managing
        if (!array_key_exists('enabled', $config)) {
            $config['enabled'] = null;
        }

        return $config;
    }

    /*******************************************
     * STATES
     *******************************************/

    /**
     * @param Token $record
     * @return bool
     */
    public function disable(Token $record)
    {
        $record->enabled = false;
        return $record->save(true, ['enabled']);
    }

    /**
     * @param Token $model
     * @return bool
     */
    public function enable(Token $model)
    {
        $model->enabled = true;
        return $model->save(true, ['enabled']);
    }
}
