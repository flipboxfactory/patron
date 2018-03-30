<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\services;

use Craft;
use flipbox\patron\Patron;
use yii\base\Component;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Session extends Component
{
    /**
     * @return string
     */
    public static function stateKey(): string
    {
        return md5('Craft.' . Patron::class . '.' . Craft::$app->id . '.State');
    }

    /**
     * @return string
     */
    public static function providerKey(): string
    {
        return md5('Craft.' . Patron::class . '.' . Craft::$app->id . '.Provider');
    }

    /**
     * @return string
     */
    public static function redirectKey(): string
    {
        return md5('Craft.' . Patron::class . '.' . Craft::$app->id . '.Redirect');
    }

    /**
     * @return static
     */
    public function removeAll()
    {
        return $this->removeProvider()
            ->removeState()
            ->removeRedirectUrl();
    }

    /**
     * @return string|null
     */
    public function getState()
    {
        return Craft::$app->getSession()->get(static::stateKey());
    }

    /**
     * @param string $value
     * @return static
     */
    public function setState(string $value)
    {
        Craft::$app->getSession()->set(static::stateKey(), $value);
        return $this;
    }

    /**
     * @return static
     */
    public function removeState()
    {
        Craft::$app->getSession()->remove(static::stateKey());
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProvider()
    {
        return Craft::$app->getSession()->get(static::providerKey());
    }

    /**
     * @param string $value
     * @return static
     */
    public function setProvider(string $value)
    {
        Craft::$app->getSession()->set(static::providerKey(), $value);
        return $this;
    }

    /**
     * @return static
     */
    public function removeProvider()
    {
        Craft::$app->getSession()->remove(static::providerKey());
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRedirectUrl()
    {
        return Craft::$app->getSession()->get(static::redirectKey());
    }

    /**
     * @param string $value
     * @return static
     */
    public function setRedirectUrl(string $value)
    {
        Craft::$app->getSession()->set(static::redirectKey(), $value);
        return $this;
    }

    /**
     * @return static
     */
    public function removeRedirectUrl()
    {
        Craft::$app->getSession()->remove(static::redirectKey());
        return $this;
    }
}
