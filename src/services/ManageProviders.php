<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\services;

use craft\helpers\ArrayHelper;
use flipbox\ember\services\traits\records\AccessorByString;
use flipbox\patron\db\ProviderQuery;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
use yii\base\Component;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method ProviderQuery getQuery($config = [])
 * @method Provider create(array $attributes = [])
 * @method Provider find($identifier)
 * @method Provider get($identifier)
 * @method Provider findByString($identifier)
 * @method Provider getByString($identifier)
 * @method Provider findByCondition($condition = [])
 * @method Provider getByCondition($condition = [])
 * @method Provider findByCriteria($criteria = [])
 * @method Provider getByCriteria($criteria = [])
 * @method Provider[] findAll()
 * @method Provider[] findAllByCondition($condition = [])
 * @method Provider[] getAllByCondition($condition = [])
 * @method Provider[] findAllByCriteria($criteria = [])
 * @method Provider[] getAllByCriteria($criteria = [])
 */
class ManageProviders extends Component
{
    use AccessorByString {
        buildQueryFromCondition as parentBuildQueryFromCondition;
    }

    /**
     * @param array $condition
     * @return QueryInterface
     */
    protected function buildQueryFromCondition($condition = []): QueryInterface
    {
        if (is_numeric($condition)) {
            $condition = ['id' => $condition];
        }

        return $this->parentBuildQueryFromCondition($condition);
    }

    /**
     * @inheritdoc
     */
    public static function recordClass(): string
    {
        return Provider::class;
    }

    /**
     * @inheritdoc
     */
    public static function stringProperty(): string
    {
        return 'handle';
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

        // Allow all environments when managing
        if (!array_key_exists('environment', $config)) {
            $config['environment'] = null;
        }

        return $config;
    }

    /*******************************************
     * ENCRYPTION
     *******************************************/

    /**
     * @param bool $changeTo
     * @return void
     */
    public function changeEncryption(bool $changeTo)
    {
        // Temp
        Patron::getInstance()->getSettings()->encryptStorageData = !$changeTo;

        // Get current providers
        $records = $this->findAll();

        // Temp
        Patron::getInstance()->getSettings()->encryptStorageData = $changeTo;

        // Iterate and save
        foreach ($records as $record) {
            Patron::info(
                'Altering Provider::$clientSecret encryption preferences'
            );

            $record->save();
        }
    }

    /*******************************************
     * STATES
     *******************************************/

    /**
     * @param Provider $record
     * @return bool
     */
    public function disable(Provider $record)
    {
        $record->enabled = false;
        return $record->save(true, ['enabled']);
    }

    /**
     * @param Provider $record
     * @return bool
     */
    public function enable(Provider $record)
    {
        $record->enabled = true;
        return $record->save(true, ['enabled']);
    }
}
