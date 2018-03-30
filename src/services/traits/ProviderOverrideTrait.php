<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\services\traits;

use Craft;
use flipbox\patron\Patron;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait ProviderOverrideTrait
{
    /**
     * @var array|null
     */
    private $overrides;

    /**
     * Returns any custom volume config values.
     *
     * @param string $handle The volume handle
     *
     * @return array|null
     */
    protected function getOverrides(string $handle)
    {
        if ($this->overrides === null) {
            $this->overrides = Craft::$app->getConfig()->getConfigFromFile(
                Patron::getInstance()->getSettings()->getProviderOverrideFileName()
            );
        }

        return $this->overrides[$handle] ?? null;
    }

    /**
     * @param array $config
     * @return array
     */
    protected function mergeOverrides(array $config)
    {
        if (!empty($config['handle']) && ($overrides = $this->getOverrides($config['handle'])) !== null) {
            $config = array_merge($config, $overrides);
        }

        return $config;
    }
}
