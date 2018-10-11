<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\services;

use flipbox\ember\services\traits\records\Accessor;
use flipbox\patron\db\ProviderQuery;
use flipbox\patron\records\ProviderLock;
use yii\base\Component;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method ProviderQuery getQuery($config = [])
 * @method ProviderLock create(array $attributes = [])
 * @method ProviderLock find($identifier)
 * @method ProviderLock get($identifier)
 * @method ProviderLock findByCondition($condition = [])
 * @method ProviderLock getByCondition($condition = [])
 * @method ProviderLock findByCriteria($criteria = [])
 * @method ProviderLock getByCriteria($criteria = [])
 * @method ProviderLock[] findAllByCondition($condition = [])
 * @method ProviderLock[] getAllByCondition($condition = [])
 * @method ProviderLock[] findAllByCriteria($criteria = [])
 * @method ProviderLock[] getAllByCriteria($criteria = [])
 */
class ProviderLocks extends Component
{
    use Accessor;

    /**
     * @inheritdoc
     */
    public static function recordClass(): string
    {
        return ProviderLock::class;
    }


    /*******************************************
     * ASSOCIATE / DISSOCIATE
     *******************************************/

    /**
     * @param ProviderLock $record
     * @return bool
     * @throws \Exception
     */
    public function associate(
        ProviderLock $record
    ): bool {
        if (true === $this->existingAssociation($record)) {
            $reOrder = true;
        }

        return $record->save();
    }

    /**
     * @param ProviderLock $record
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function dissociate(
        ProviderLock $record
    ): bool {
        if (false === $this->existingAssociation($record)) {
            return true;
        }

        return (bool)$record->delete();
    }


    /*******************************************
     * EXISTING
     *******************************************/

    /**
     * @param ProviderLock $record
     * @return bool
     */
    protected function existingAssociation(
        ProviderLock $record
    ): bool {
        if (null !== ($existing = $this->lookupAssociation($record))) {
            $record->setOldAttributes(
                $existing->getOldAttributes()
            );
        }

        return $existing !== null;
    }

    /**
     * @param ProviderLock $record
     * @return ProviderLock|null
     */
    protected function lookupAssociation(
        ProviderLock $record
    ) {
        $model = $this->getQuery()
            ->where([
                'providerId' => $record->providerId,
                'pluginId' => $record->pluginId
            ])->one();

        return $model instanceof ProviderLock ? $model : null;
    }

    /**
     * @param int $providerId
     * @param int $pluginId
     * @return bool
     * @throws \Throwable
     */
    public function associateByIds(
        int $providerId,
        int $pluginId
    ): bool {
        return $this->associate(
            $this->create([
                'providerId' => $providerId,
                'pluginId' => $pluginId
            ])
        );
    }

    /**
     * @param int $providerId
     * @param int $pluginId
     * @return bool
     * @throws \Throwable
     */
    public function dissociateByIds(
        int $providerId,
        int $pluginId
    ): bool {
        return $this->dissociate(
            $this->create([
                'providerId' => $providerId,
                'pluginId' => $pluginId
            ])
        );
    }
}
