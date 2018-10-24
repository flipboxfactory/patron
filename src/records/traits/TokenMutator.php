<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\records\traits;

use Craft;
use flipbox\ember\helpers\ObjectHelper;
use flipbox\patron\Patron;
use flipbox\patron\records\Token;
use yii\db\ActiveQueryInterface;

/**
 * @property int|null $tokenId
 * @property Token|null $token
 * @property ActiveQueryInterface $tokenRecord
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait TokenMutator
{
    /**
     * @var Token|null
     */
    private $token;

    /**
     * Set associated tokenId
     *
     * @param $id
     * @return $this
     */
    public function setTokenId(int $id)
    {
        $this->tokenId = $id;
        return $this;
    }

    /**
     * Get associated tokenId
     *
     * @return int|null
     */
    public function getTokenId()
    {
        $id = $this->getAttribute('tokenId');
        if (null === $id && null !== $this->token) {
            $id = $this->token->id;
            $this->setAttribute('tokenId', $id);
        }

        return $id;
    }

    /**
     * Associate a token
     *
     * @param mixed $token
     * @return $this
     */
    public function setToken($token = null)
    {
        $this->token = null;

        if (null === ($token = $this->internalResolveToken($token))) {
            $this->token = $this->tokenId = null;
        } else {
            $this->tokenId = $token->id;
            $this->token = $token;
        }

        return $this;
    }

    /**
     * @return Token|null
     */
    public function getToken()
    {
        if ($this->token === null) {
            $token = $this->resolveToken();
            $this->setToken($token);
            return $token;
        }

        $tokenId = $this->tokenId;
        if ($tokenId !== null &&
            $tokenId !== $this->token->id
        ) {
            $this->token = null;
            return $this->getToken();
        }

        return $this->token;
    }

    /**
     * @return Token|null
     */
    protected function resolveToken()
    {
        if (null !== ($model = $this->resolveTokenFromRelation())) {
            return $model;
        }

        if (null !== ($model = $this->resolveTokenFromId())) {
            return $model;
        }

        return null;
    }

    /**
     * @return Token|null
     */
    private function resolveTokenFromRelation()
    {
        if (false === $this->isRelationPopulated('tokenRecord')) {
            return null;
        }

        /** @var Token $record */
        if (null === ($record = $this->getRelation('tokenRecord'))) {
            return null;
        }

        return $record;
    }

    /**
     * @return Token|null
     */
    private function resolveTokenFromId()
    {
        if (null === $this->tokenId) {
            return null;
        }

        return Patron::getInstance()->manageTokens()->find($this->tokenId);
    }

    /**
     * @param $token
     * @return Token|null
     */
    protected function internalResolveToken($token = null)
    {
        if ($token instanceof Token) {
            return $token;
        }

        if (is_numeric($token) || is_string($token)) {
            return Patron::getInstance()->manageTokens()->find($token);
        }

        try {
            $object = Craft::createObject(Token::class, [$token]);
        } catch (\Exception $e) {
            $object = new Token();
            ObjectHelper::populate(
                $object,
                $token
            );
        }

        /** @var Token $object */
        return $object;
    }

    /**
     * Returns the associated token record.
     *
     * @return ActiveQueryInterface
     */
    protected function getTokenRecord(): ActiveQueryInterface
    {
        return $this->hasOne(
            Token::class,
            ['id' => 'tokenId']
        );
    }
}
