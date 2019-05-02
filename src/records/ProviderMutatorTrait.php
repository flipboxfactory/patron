<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records;

use Craft;
use flipbox\craft\ember\helpers\ObjectHelper;
use flipbox\craft\ember\records\ActiveRecordTrait;
use yii\db\ActiveQueryInterface;

/**
 * @property int|null $providerId
 * @property Provider|null $provider
 * @property ActiveQueryInterface $providerRecord
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait ProviderMutatorTrait
{
    use ActiveRecordTrait;

    /**
     * @var Provider|null
     */
    private $provider;

    /**
     * Set associated providerId
     *
     * @param $id
     * @return $this
     */
    public function setProviderId(int $id)
    {
        $this->providerId = $id;
        return $this;
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
     * Associate a provider
     *
     * @param mixed $provider
     * @return $this
     */
    public function setProvider($provider = null)
    {
        $this->provider = null;

        if (null === ($provider = $this->internalResolveProvider($provider))) {
            $this->provider = $this->providerId = null;
        } else {
            $this->providerId = $provider->id;
            $this->provider = $provider;
        }

        return $this;
    }

    /**
     * @return Provider|null
     */
    public function getProvider()
    {
        if ($this->provider === null) {
            $provider = $this->resolveProvider();
            $this->setProvider($provider);
            return $provider;
        }

        $providerId = $this->providerId;
        if ($providerId !== null &&
            $providerId !== $this->provider->id
        ) {
            $this->provider = null;
            return $this->getProvider();
        }

        return $this->provider;
    }

    /**
     * @return Provider|null
     */
    protected function resolveProvider()
    {
        if (null !== ($model = $this->resolveProviderFromRelation())) {
            return $model;
        }

        if (null !== ($model = $this->resolveProviderFromId())) {
            return $model;
        }

        return null;
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
        if (null === ($record = $this->getRelation('providerRecord'))) {
            return null;
        }

        return $record;
    }

    /**
     * @return Provider|null
     */
    private function resolveProviderFromId()
    {
        if (null === $this->providerId) {
            return null;
        }

        return Provider::findOne(['id' => $this->providerId]);
    }

    /**
     * @param $provider
     * @return Provider|null
     */
    protected function internalResolveProvider($provider = null)
    {
        if ($provider instanceof Provider) {
            return $provider;
        }

        if (is_numeric($provider) || is_string($provider)) {
            return Provider::findOne([
                'enabled' => null,
                is_numeric($provider) ? 'id' : 'handle' => $provider
            ]);
        }

        try {
            $object = Craft::createObject(Provider::class, [$provider]);
        } catch (\Exception $e) {
            $object = new Provider();
            ObjectHelper::populate(
                $object,
                $provider
            );
        }

        /** @var Provider $object */
        return $object;
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
