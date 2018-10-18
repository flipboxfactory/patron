<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\services;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\services\traits\records\AccessorByString;
use Flipbox\OAuth2\Client\Provider\Guardian as GuardianProvider;
use flipbox\patron\db\ProviderQuery;
use flipbox\patron\events\RegisterProviderSettings;
use flipbox\patron\Patron;
use flipbox\patron\providers\Base;
use flipbox\patron\providers\Facebook as FacebookSettings;
use flipbox\patron\providers\Guardian as GuardianSettings;
use flipbox\patron\providers\SettingsInterface;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderEnvironment;
use League\OAuth2\Client\Provider\Facebook as FacebookProvider;
use yii\base\Component;
use yii\base\InvalidConfigException;

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
 * @method Provider[] findAllByCondition($condition = [])
 * @method Provider[] getAllByCondition($condition = [])
 * @method Provider[] findAllByCriteria($criteria = [])
 * @method Provider[] getAllByCriteria($criteria = [])
 */
class ManageProviders extends Component
{
    use AccessorByString;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Register setting handlers for providers
        RegisterProviderSettings::on(
            GuardianProvider::class,
            RegisterProviderSettings::REGISTER_SETTINGS,
            function (RegisterProviderSettings $event) {
                $event->class = GuardianSettings::class;
            }
        );

        RegisterProviderSettings::on(
            FacebookProvider::class,
            RegisterProviderSettings::REGISTER_SETTINGS,
            function (RegisterProviderSettings $event) {
                $event->class = FacebookSettings::class;
            }
        );
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

    /**
     * @param Provider $provider
     * @param array $settings
     * @return SettingsInterface
     * @throws InvalidConfigException
     */
    public function resolveSettings(Provider $provider, $settings = []): SettingsInterface
    {
        return $this->createSettings($provider->class, $settings);
    }

    /**
     * @param string $providerClass
     * @return mixed
     */
    protected function resolveSettingsClass(string $providerClass = null): string
    {
        if (null === $providerClass) {
            return Base::class;
        }

        $event = new RegisterProviderSettings();

        RegisterProviderSettings::trigger(
            $providerClass,
            RegisterProviderSettings::REGISTER_SETTINGS,
            $event
        );

        if (!$this->isSettingsInstance($event->class)) {
            return Base::class;
        }

        return $event->class;
    }

    /**
     * Check settings instance
     *
     * @param $class
     * @return bool
     */
    private function isSettingsInstance($class): bool
    {
        return $class instanceof SettingsInterface || is_subclass_of($class, SettingsInterface::class);
    }

    /**
     * @param $providerClass
     * @param array $settings
     * @return SettingsInterface
     * @throws InvalidConfigException
     */
    protected function createSettings($providerClass, $settings = []): SettingsInterface
    {
        if (is_string($settings)) {
            $settings = Json::decodeIfJson($settings);
        }

        if (!is_array($settings)) {
            $settings = ArrayHelper::toArray($settings, [], true);
        }

        $settings['class'] = $this->resolveSettingsClass($providerClass);

        /** @var SettingsInterface $model */
        $model = ModelHelper::create($settings, SettingsInterface::class);

        return $model;
    }


    /*******************************************
     * ENVIRONMENTS
     *******************************************/

    /**
     * @param Provider $provider
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveEnvironments(Provider $provider)
    {
        $successful = true;

        /** @var ProviderEnvironment[] $allRecords */
        $allRecords = $provider->getEnvironments()
            ->all();

        foreach ($this->resolveEnvironments($provider) as $model) {
            ArrayHelper::remove($allRecords, $model->environment);
            $model->providerId = $provider->getId();

            if (!$model->save()) {
                $successful = false;
                // Log the errors
                $error = Craft::t(
                    'patron',
                    "Couldn't save environment due to validation errors:"
                );
                foreach ($model->getFirstErrors() as $attributeError) {
                    $error .= "\n- " . Craft::t('patron', $attributeError);
                }

                $provider->addError('sites', $error);
            }
        }

        // Delete old records
        foreach ($allRecords as $record) {
            $record->delete();
        }

        return $successful;
    }

    /**
     * @param Provider $provider
     * @return ProviderEnvironment[]
     */
    protected function defaultEnvironments(Provider $provider): array
    {
        $environments = [];

        foreach (Patron::getInstance()->getSettings()->getDefaultEnvironments() as $environment) {
            $environments[$environment] = new ProviderEnvironment([
                'providerId' => $provider->getId(),
                'environment' => $environment
            ]);
        }

        return $environments;
    }

    /**
     * @param Provider $provider
     * @return array
     */
    protected function resolveEnvironments(Provider $provider): array
    {
        $environments = $provider->environments;

        if (empty($environments)) {
            $environments = $this->defaultEnvironments($provider);
        }

        return $environments;
    }

    /*******************************************
     * ENCRYPTION
     *******************************************/

    /**
     * @param Provider $record
     * @return bool
     */
    public function changeEncryption(bool $changeTo)
    {
        // Temp
        Patron::getInstance()->getSettings()->encryptStorageData = !$changeTo;

        // Get current providers
        $records = Patron::getInstance()->manageProviders->findAll();

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
