<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\actions\provider;

use flipbox\craft\ember\actions\records\RecordIndex;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\patron\records\Provider;
use yii\db\QueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ProviderIndex extends RecordIndex
{
    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    protected function createQuery(array $config = []): QueryInterface
    {
        $query = Provider::find();

        QueryHelper::configure(
            $query,
            $config
        );

        return $query;
    }
}
