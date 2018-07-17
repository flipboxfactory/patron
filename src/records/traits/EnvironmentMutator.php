<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records\traits;

/**
 * @property string|null $environment
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait EnvironmentMutator
{
    /**
     * @inheritdoc
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @inheritdoc
     */
    public function setEnvironment($environment)
    {
        $this->environment = (int)$environment;
        return $this;
    }
}
