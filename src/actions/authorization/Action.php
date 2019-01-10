<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\authorization;

use flipbox\craft\ember\actions\CheckAccessTrait;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Action extends \yii\base\Action
{
    use CheckAccessTrait;

    /**
     *
     * Exception handling.
     *
     * @param \Closure $callback
     * @return mixed
     * @throws HttpException
     */
    protected function handleExceptions(\Closure $callback)
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            throw new HttpException(500, $e->getMessage());
        } catch (\Throwable $e) {
            throw new HttpException(500, $e->getMessage());
        }
    }
}
