<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\helpers;

use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use flipbox\patron\records\Provider;
use League\OAuth2\Client\Provider\AbstractProvider;
use ReflectionClass;
use yii\db\Query;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ProviderHelper
{
    /**
     * @param $provider
     * @return string
     * @throws \ReflectionException
     */
    public static function displayName($provider): string
    {
        $reflect = new ReflectionClass(
            $provider
        );

        // Split capital letters
        $parts = preg_split("/(?<=[a-z])(?![a-z])/", $reflect->getShortName(), -1, PREG_SPLIT_NO_EMPTY);

        // Assemble
        return StringHelper::toString($parts, ' ');
    }

    /**
     * @param AbstractProvider $provider
     * @param array $properties
     * @return array
     * @throws \ReflectionException
     */
    public static function getProtectedProperties(
        AbstractProvider $provider,
        array $properties
    ): array {
        $reflection = new ReflectionClass($provider);

        $values = [];

        foreach ($properties as $property) {
            $values[] = static::getProtectedProperty(
                $provider,
                $reflection,
                $property
            );
        }

        return $values;
    }

    /*******************************************
     * GET ID
     *******************************************/

    /**
     * @param AbstractProvider $provider
     * @return int|null
     * @throws \ReflectionException
     */
    public static function lookupId(AbstractProvider $provider)
    {
        list($clientId, $clientSecret) = ProviderHelper::getProtectedProperties(
            $provider,
            ['clientId', 'clientSecret']
        );

        $condition = [
            'class' => get_class($provider),
        ];

        // Not all, but those w/ same type
        $providers = Provider::findAll($condition);

        // Find those w/ matching clientId / clientSecret
        $matchingProvider = ArrayHelper::firstWhere(
            ArrayHelper::where($providers, 'clientId', $clientId, true),
            'clientSecret', $clientSecret, true
        );

        if (!$matchingProvider) {
            return null;
        }

        return (int)$matchingProvider->id;
    }


    /**
     * @param AbstractProvider $provider
     * @param ReflectionClass $reflectionClass
     * @param string $property
     * @return mixed
     * @throws \ReflectionException
     */
    public static function getProtectedProperty(
        AbstractProvider $provider,
        ReflectionClass $reflectionClass,
        string $property
    ) {
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($provider);
    }
}
