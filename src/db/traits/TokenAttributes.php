<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/patron/domains/
 */

namespace flipbox\patron\db\traits;

use craft\helpers\Db;
use flipbox\patron\records\Token;
use flipbox\patron\records\TokenEnvironment;
use yii\db\Expression;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait TokenAttributes
{
    /**
     * @var bool|null The enabled state
     */
    public $enabled = true;

    /**
     * @var int|int[]|false|null The model ID(s). Prefix IDs with "not " to exclude them.
     */
    public $providerId;

    /**
     * @var string|string[]|null The access token(s). Prefix IDs with "not " to exclude them.
     */
    public $accessToken;

    /**
     * @var string|string[]|null The refresh token(s). Prefix IDs with "not " to exclude them.
     */
    public $refreshToken;

    /**
     * @var string|string[]|null The environment(s). Prefix with "not " to exclude them.
     */
    public $environment;

    /**
     * Adds an additional WHERE condition to the existing one.
     * The new condition and the existing one will be joined using the `AND` operator.
     * @param string|array|Expression $condition the new WHERE condition. Please refer to [[where()]]
     * on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return $this the query object itself
     * @see where()
     * @see orWhere()
     */
    abstract public function andWhere($condition, $params = []);

    /**
     * @param $enabled
     * @return $this
     */
    public function enabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function providerId($id)
    {
        $this->providerId = $id;
        return $this;
    }

    /**
     * @param $accessToken
     * @return $this
     */
    public function accessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @param $refreshToken
     * @return $this
     */
    public function refreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * @param $environment
     * @return $this
     */
    public function environment($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     *
     */
    protected function applyConditions()
    {
        if ($this->enabled !== null) {
            $this->andWhere(Db::parseParam('enabled', $this->enabled));
        }

        $attributes = ['providerId', 'accessToken', 'refreshToken'];

        foreach ($attributes as $attribute) {
            if (($value = $this->{$attribute}) !== null) {
                $this->andWhere(Db::parseParam($attribute, $value));
            }
        }

        $this->applyEnvironmentParam();
    }

    /**
     * Apply environment params
     */
    protected function applyEnvironmentParam()
    {
        if (empty($this->environment)) {
            return;
        }

        $alias = TokenEnvironment::tableAlias();

        $this->leftJoin(
            TokenEnvironment::tableName() . ' ' . $alias,
            '[[' . $alias . '.tokenId]] = [[' . Token::tableAlias() . '.id]]'
        );
        $this->andWhere(
            Db::parseParam($alias . '.environment', $this->environment)
        );
    }
}
