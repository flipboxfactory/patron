<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records\traits;

use Craft;
use flipbox\ember\helpers\ObjectHelper;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;

/**
 * @property int|null $providerId
 * @property Provider|null $provider
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait ProviderMutator
{
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
        if (null === $this->providerId && null !== $this->provider) {
            $this->providerId = $this->provider->id;
        }

        return $this->providerId;
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
        if ($model = $this->resolveProviderFromId()) {
            return $model;
        }

        return null;
    }

    /**
     * @return Provider|null
     */
    private function resolveProviderFromId()
    {
        if (null === $this->providerId) {
            return null;
        }

        return Patron::getInstance()->manageProviders()->get($this->providerId);
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
            return Patron::getInstance()->manageProviders()->find($provider);
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
}
