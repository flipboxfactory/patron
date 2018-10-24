<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records\traits;

use Craft;
use flipbox\ember\helpers\ObjectHelper;
use flipbox\patron\records\ProviderInstance;
use yii\db\ActiveQueryInterface;

/**
 * @property int|null $instanceId
 * @property ProviderInstance|null $instance
 * @property ActiveQueryInterface $instanceRecord
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait InstanceMutator
{
    /**
     * @var ProviderInstance|null
     */
    private $instance;

    /**
     * Set associated instanceId
     *
     * @param $id
     * @return $this
     */
    public function setInstanceId(int $id)
    {
        $this->instanceId = $id;
        return $this;
    }

    /**
     * Get associated instanceId
     *
     * @return int|null
     */
    public function getInstanceId()
    {
        $id = $this->getAttribute('instanceId');
        if (null === $id && null !== $this->instance) {
            $id = $this->instance->id;
            $this->setAttribute('instanceId', $id);
        }

        return $id;
    }

    /**
     * Associate a instance
     *
     * @param mixed $instance
     * @return $this
     */
    public function setInstance($instance = null)
    {
        $this->instance = null;

        if (null === ($instance = $this->internalResolveInstance($instance))) {
            $this->instance = $this->instanceId = null;
        } else {
            $this->instanceId = $instance->id;
            $this->instance = $instance;
        }

        return $this;
    }

    /**
     * @return ProviderInstance|null
     */
    public function getInstance()
    {
        if ($this->instance === null) {
            $instance = $this->resolveInstance();
            $this->setInstance($instance);
            return $instance;
        }

        $instanceId = $this->instanceId;
        if ($instanceId !== null &&
            $instanceId !== $this->instance->id
        ) {
            $this->instance = null;
            return $this->getInstance();
        }

        return $this->instance;
    }

    /**
     * @return ProviderInstance|null
     */
    protected function resolveInstance()
    {
        if (null !== ($model = $this->resolveInstanceFromRelation())) {
            return $model;
        }

        if (null !== ($model = $this->resolveInstanceFromId())) {
            return $model;
        }

        return null;
    }

    /**
     * @return ProviderInstance|null
     */
    private function resolveInstanceFromRelation()
    {
        if (false === $this->isRelationPopulated('instanceRecord')) {
            return null;
        }

        /** @var ProviderInstance $record */
        if (null === ($record = $this->getRelation('instanceRecord'))) {
            return null;
        }

        return $record;
    }

    /**
     * @return ProviderInstance|null
     */
    private function resolveInstanceFromId()
    {
        if (null === $this->instanceId) {
            return null;
        }

        return ProviderInstance::findOne($this->instanceId);
    }

    /**
     * @param $instance
     * @return ProviderInstance|null
     */
    protected function internalResolveInstance($instance = null)
    {
        if ($instance instanceof ProviderInstance) {
            return $instance;
        }

        if (is_numeric($instance) || is_string($instance)) {
            return ProviderInstance::findOne($instance);
        }

        try {
            $object = Craft::createObject(ProviderInstance::class, [$instance]);
        } catch (\Exception $e) {
            $object = new ProviderInstance();
            ObjectHelper::populate(
                $object,
                $instance
            );
        }

        /** @var ProviderInstance $object */
        return $object;
    }

    /**
     * Returns the associated instance record.
     *
     * @return ActiveQueryInterface
     */
    protected function getInstanceRecord(): ActiveQueryInterface
    {
        return $this->hasOne(
            ProviderInstance::class,
            ['id' => 'instanceId']
        );
    }
}
