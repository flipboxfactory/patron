<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records\traits;

use flipbox\ember\records\traits\ActiveRecord;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait ProviderAttribute
{
    use ActiveRecord,
        ProviderRules,
        ProviderMutator {
            resolveProvider as parentResolveProvider;
    }

    /**
     * Get associated providerId
     *
     * @return int|null
     */
    public function getProviderId()
    {
        $id = $this->getAttribute('providerId');
        if (null === $id && null !== $this->provider) {
            $id = $this->provider->id;
            $this->setAttribute('providerId', $id);
        }

        return $id;
    }

    /**
     * @return Provider|null
     */
    protected function resolveProvider()
    {
        if ($model = $this->resolveProviderFromRelation()) {
            return $model;
        }

        return $this->parentResolveProvider();
    }

    /**
     * @return Provider|null
     */
    private function resolveProviderFromRelation()
    {
        if (false === $this->isRelationPopulated('providerRecord')) {
            return null;
        }

        /** @var Provider $record */
        $record = $this->getRelation('providerRecord');
        if (null === $record) {
            return null;
        }

        return Patron::getInstance()->manageProviders()->find($record->id);
    }

    /**
     * Returns the associated provider record.
     *
     * @return ActiveQueryInterface
     */
    protected function getProviderRecord(): ActiveQueryInterface
    {
        return $this->hasOne(
            Provider::class,
            ['id' => 'providerId']
        );
    }
}
