<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\authorization;

use Craft;
use flipbox\patron\Patron;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Callback extends Action
{
    use traits\Lookup;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $patron = Patron::getInstance();

        // Get code
        if (!$code = Craft::$app->getRequest()->getParam('code')) {
            return $this->handleInvalidCodeResponse();
        }

        // Validate state
        $state = Craft::$app->getRequest()->getParam('state');
        if (!$this->isValidState($state)) {
            return $this->handleInvalidStateResponse($state);
        }

        // Get provider
        $identifier = $patron->getSession()->getProvider();
        if (!$identifier || (!$provider = $this->find($identifier))) {
            return $this->handleNotFoundResponse();
        }

        return $this->runInternal(
            $code,
            $provider
        );
    }

    /**
     * @param $code
     * @param $identifier
     * @param AbstractProvider $provider
     * @return AccessToken|mixed
     * @throws HttpException
     */
    public function runInternal(
        $code,
        AbstractProvider $provider
    ) {
        if (($access = $this->checkAccess($provider)) !== true) {
            return $access;
        }

        return $this->performAction($code, $provider);
    }

    /**
     * @param $code
     * @param AbstractProvider $provider
     * @return AccessToken
     * @throws HttpException
     */
    public function performAction(
        $code,
        AbstractProvider $provider
    ): AccessToken {

        return $this->handleExceptions(function () use ($code, $provider) {

            // Get token via authorization code grant.
            $accessToken = $provider->getAccessToken(
                'authorization_code',
                [
                    'code' => $code
                ]
            );

            // Save token
            Patron::getInstance()->getTokens()->persistNewToken(
                $accessToken,
                $provider
            );

            return $accessToken;
        });
    }

    /**
     * @param string|null $state
     * @return bool
     */
    protected function isValidState(string $state = null): bool
    {
        if ($state !== Patron::getInstance()->getSession()->getState()) {
            return false;
        }
        return true;
    }

    /**
     * @param string|null $url
     * @return string
     */
    protected function resolveRedirectUrl(string $url = null)
    {
        if (empty($url)) {
            $url = Patron::getInstance()->getSettings()->getCallbackUrl();
        }

        return $url;
    }


    /*******************************************
     * INVALID STATE RESPONSE
     *******************************************/

    /**
     * @param string|null $state
     * @throws HttpException
     */
    protected function handleInvalidStateResponse(string $state = null)
    {
        Patron::warning(Craft::t(
            'patron',
            "Invalid state: '{state}'",
            [
                '{state}' => $state
            ]
        ));

        throw new HttpException(
            $this->statusCodeInvalidState(),
            $this->messageInvalidState()
        );
    }

    /**
     * @return string
     */
    protected function messageInvalidState(): string
    {
        return Craft::t('patron', 'Invalid state.');
    }

    /**
     * HTTP not found response code
     *
     * @return int
     */
    protected function statusCodeInvalidState(): int
    {
        return 404;
    }


    /*******************************************
     * USER NOT FOUND RESPONSE
     *******************************************/

    /**
     * @throws HttpException
     */
    protected function handleUserNotFoundResponse()
    {
        throw new HttpException(
            $this->statusCodeUserNotFound(),
            $this->messageUserNotFound()
        );
    }

    /**
     * @return string
     */
    protected function messageUserNotFound(): string
    {
        return Craft::t('patron', 'Unable to find user.');
    }

    /**
     * HTTP not found response code
     *
     * @return int
     */
    protected function statusCodeUserNotFound(): int
    {
        return 404;
    }


    /*******************************************
     * INVALID CODE RESPONSE
     *******************************************/

    /**
     * @throws HttpException
     */
    protected function handleInvalidCodeResponse()
    {
        throw new HttpException(
            $this->statusCodeInvalidCode(),
            $this->messageInvalidCode()
        );
    }

    /**
     * @return string
     */
    protected function messageInvalidCode(): string
    {
        return Craft::t('patron', 'Invalid code.');
    }

    /**
     * HTTP not found response code
     *
     * @return int
     */
    protected function statusCodeInvalidCode(): int
    {
        return 404;
    }
}
