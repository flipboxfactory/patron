<?php

namespace flipbox\patron\cp\controllers;

use Craft;
use flipbox\patron\actions\authorization\Authorize;

class AuthorizationController extends AbstractController
{
    /**
     * @inheritdoc
     */
    protected function verbs(): array
    {
        return [
            'authorize' => ['get']
        ];
    }

    /**
     * @param int|null $id
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAuthorize(int $id = null)
    {
        if (null === $id) {
            $id = (int)Craft::$app->getRequest()->getBodyParam('id');
        }

        $action = Craft::createObject([
            'class' => Authorize::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'authorize',
            $this
        ]);

        return $action->runWithParams([
            'id' => $id
        ]);
    }

    /**
     * @inheritDoc
     */
    public function checkAdminAccess(): bool
    {
        $this->requireAdmin(false);
        return true;
    }
}
