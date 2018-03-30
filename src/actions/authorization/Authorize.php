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
use yii\web\Controller;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property Controller $controller
 */
class Authorize extends Action
{
    use traits\Lookup;

    /**
     * @inheritdoc
     */
    public function run(int $id)
    {
        if (!$object = $this->find($id)) {
            return $this->handleNotFoundResponse();
        }

        // Save provider to session
        Patron::getInstance()->getSession()->setProvider(
            $id
        );

        return $this->runInternal($object, $this->getScopes());
    }

    /**
     * @return array
     */
    protected function getScopes(): array
    {
        if (!$scopes = Craft::$app->getRequest()->getParam('scope')) {
            return [];
        }

        return $scopes;
    }

    /**
     * @param AbstractProvider $provider
     * @param array $scopes
     * @return mixed|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function runInternal(AbstractProvider $provider, array $scopes)
    {
        $request = Craft::$app->getRequest();

        // Set redirect
        Patron::getInstance()->getSession()->setRedirectUrl(
            $request->getParam('redirect', $request->getReferrer())
        );

        // Check access
        if (($access = $this->checkAccess($provider, $scopes)) !== true) {
            return $access;
        }

        return $this->performAction($provider, $scopes);
    }

    /**
     * @param AbstractProvider $provider
     * @param array $scopes
     * @return mixed
     * @throws \yii\web\HttpException
     */
    protected function performAction(AbstractProvider $provider, array $scopes)
    {
        return $this->handleExceptions(function () use ($provider, $scopes) {
            $options = [];

            if (!empty($scopes)) {
                $options['scope'] = $scopes;
            }

            // Get url (this ensure all params ... such as state)
            $authorizationUrl = $provider->getAuthorizationUrl($options);

            // Save state to session
            Patron::getInstance()->getSession()->setState(
                $provider->getState()
            );

            return $this->controller->redirect($authorizationUrl);
        });
    }
}
