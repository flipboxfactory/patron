<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records\traits;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\ember\helpers\ObjectHelper;
use flipbox\ember\helpers\QueryHelper;
use flipbox\ember\records\traits\ActiveRecord as ActiveRecordTrait;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait RelatedEnvironmentsAttribute
{
    use ActiveRecordTrait;

    /**
     * @var bool
     */
    public $autoSaveEnvironments = true;

    /**
     * Environments that are temporarily set during the save process
     *
     * @var null|array
     */
    private $insertEnvironments;

    /**
     * @return string
     */
    abstract protected static function environmentRecordClass(): string;

    /**
     * @param array $config
     * @return array
     */
    protected abstract function prepareEnvironmentRecordConfig(array $config = []): array;

    /**
     * @return ActiveQueryInterface
     */
    protected abstract function environmentRelationshipQuery(): ActiveQueryInterface;

    /**
     * Get all of the associated environments.
     *
     * @param array $config
     * @return \yii\db\ActiveQueryInterface|\yii\db\ActiveQuery
     */
    public function getEnvironments(array $config = [])
    {
        $query = $this->environmentRelationshipQuery();

        if (!empty($config)) {
            QueryHelper::configure(
                $query,
                $config
            );
        }

        return $query;
    }

    /**
     * @param array $environments
     * @return $this
     */
    public function setEnvironments(array $environments = [])
    {
        $environments = array_filter($environments);

        // Do nothing
        if (empty($environments) && !$this->isRelationPopulated('environments')) {
            return $this;
        }

        $currentEnvironments = ArrayHelper::index($this->environments, 'environment');

        $records = [];
        foreach ($environments as $key => $environment) {
            $environment = $this->resolveEnvironmentNEW($environment);

            // Already set ?
            if (array_key_exists($environment->getAttribute('environment'), $currentEnvironments)) {
                continue;
            }

            $records[] = $environment;
        }

        $this->populateRelation('environments', $records);
        return $this;
    }

    /**
     * @param $environment
     * @return ActiveRecord
     */
    protected function resolveEnvironmentNEW($environment)
    {
        $class = static::environmentRecordClass();

        if (is_subclass_of($environment, $class)) {
            return $environment;
        }

        // New record
        $record = new $class;

        // Force an array
        if (is_string($environment)) {
            $environment = ['environment' => $environment];
        }

        if (!is_array($environment)) {
            $environment = ArrayHelper::toArray($environment, [], false);
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ObjectHelper::populate(
            $record,
            $this->prepareEnvironmentRecordConfig($environment)
        );
    }

    /**
     * @param bool $force
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    protected function saveEnvironments(bool $force = false): bool
    {
        if ($force === false && $this->autoSaveEnvironments !== true) {
            return true;
        }

        $successful = true;

        /** @var ActiveRecord[] $allRecords */
        $allRecords = $this->getEnvironments()
            ->indexBy('environment')
            ->all();

        /** @var ActiveRecord $model */
        foreach ($this->environments as $model) {
            ArrayHelper::remove($allRecords, $model->getAttribute('environment'));

            if (!$model->save()) {
                $successful = false;

                $error = Craft::t(
                    'patron',
                    "Couldn't save environment due to validation errors:"
                );
                foreach ($model->getFirstErrors() as $attributeError) {
                    $error .= "\n- " . Craft::t('patron', $attributeError);
                }

                $this->addError('environments', $error);
            }
        }

        // Delete old records
        foreach ($allRecords as $record) {
            $record->delete();
        }

        return $successful;
    }

    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function beforeSaveEnvironments($insert): bool
    {
        if ($insert !== true ||
            $this->isRelationPopulated('environments') !== true ||
            $this->autoSaveEnvironments !== true
        ) {
            return true;
        }

        $this->insertEnvironments = $this->environments;

        return true;
    }

    /**
     * We're extracting the environments that may have been explicitly set on the record.  When the 'id'
     * attribute is updated, it removes any associated relationships.
     *
     * @inheritdoc
     * @throws \Throwable
     */
    protected function insertInternalEnvironments($attributes = null)
    {
        if (null === $this->insertEnvironments) {
            return true;
        }

        $this->setEnvironments($this->insertEnvironments);
        $this->insertEnvironments = null;

        return $this->upsertInternal($attributes);
    }
}
