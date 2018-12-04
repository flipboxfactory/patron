<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\helpers;

use Craft;
use craft\helpers\StringHelper;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderInstance;
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

        if (Patron::getInstance()->getSettings()->getEncryptStorageData() === true) {
            return self::lookupIdFromEncryptedClientSecret(
                get_class($provider),
                $clientId,
                $clientSecret
            );
        }

        return self::lookupIdFromUnencryptedClientSecret(
            get_class($provider),
            $clientId,
            $clientSecret
        );
    }

    /**
     * @param string $class
     * @param string $clientId
     * @param string $clientSecret
     * @return int|null
     */
    protected static function lookupIdFromEncryptedClientSecret(string $class, string $clientId, string $clientSecret)
    {
        $condition = [
            'class' => $class,
            'clientId' => $clientId,
        ];

        $rows = (new Query())
            ->select(['providerId', 'clientSecret'])
            ->from([ProviderInstance::tableName() . ' ' . ProviderInstance::tableAlias()])
            ->leftJoin(
                [Provider::tableName() . ' ' . Provider::tableAlias()],
                Provider::tableAlias() . '.id=providerId'
            )
            ->where($condition)
            ->all();

        foreach ($rows as $row) {
            $secret = $row['clientSecret'] ?? '';

            if ($clientSecret === ProviderHelper::decryptClientSecret($secret)) {
                return (int)$row['providerId'];
            }
        }

        return null;
    }

    /**
     * @param string $class
     * @param string $clientId
     * @param string $clientSecret
     * @return int|null
     */
    protected static function lookupIdFromUnencryptedClientSecret(string $class, string $clientId, string $clientSecret)
    {
        $condition = [
            'class' => $class,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret
        ];

        if (!$providerId = (new Query())
            ->select(['providerId'])
            ->from([ProviderInstance::tableName() . ' ' . ProviderInstance::tableAlias()])
            ->leftJoin(
                [Provider::tableName() . ' ' . Provider::tableAlias()],
                Provider::tableAlias() . '.id=providerId'
            )
            ->where($condition)
            ->scalar()
        ) {
            return null;
        }

        return (int)$providerId;
    }

    /**
     * @param string $value
     * @param bool $checkSettings
     * @return string
     */
    public static function encryptClientSecret(string $value, bool $checkSettings = true): string
    {
        if ($checkSettings === true && Patron::getInstance()->getSettings()->getEncryptStorageData() != true) {
            return $value;
        }

        return base64_encode(Craft::$app->getSecurity()->encryptByKey($value));
    }

    /**
     * @param string $value
     * @param bool $checkSettings
     * @return string
     */
    public static function decryptClientSecret(string $value, bool $checkSettings = true): string
    {
        if ($checkSettings === true && Patron::getInstance()->getSettings()->getEncryptStorageData() != true) {
            return $value;
        }

        try {
            return Craft::$app->getSecurity()->decryptByKey(
                base64_decode($value)
            );
        } catch (\Exception $e) {
            Patron::error(
                Craft::t(
                    'patron',
                    "Unable to decrypt client secret '{secret}'. Message: '{message}'",
                    [
                        'secret' => $value,
                        'message' => $e->getMessage()
                    ]
                )
            );
        }

        return $value;
    }


    /**
     * @param AbstractProvider $provider
     * @param ReflectionClass $reflectionClass
     * @param string $property
     * @return mixed
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
