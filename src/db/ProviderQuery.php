<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\db;

use craft\db\Query;
use craft\db\QueryAbortedException;
use craft\helpers\ArrayHelper;
use flipbox\ember\db\traits\AuditAttributes;
use flipbox\ember\db\traits\FixedOrderBy;
use flipbox\patron\helpers\ProviderHelper;
use flipbox\patron\Patron;
use flipbox\patron\records\Provider;
use flipbox\patron\records\ProviderInstance;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ProviderQuery extends Query
{
    use traits\ProviderAttributes,
        FixedOrderBy,
        AuditAttributes;

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        $this->orderBy = [Provider::tableAlias() . '.dateCreated' => SORT_DESC];
        $this->from = [Provider::tableName() . ' ' . Provider::tableAlias()];
        $this->select = [
            Provider::tableAlias() . '.*',
            ProviderInstance::tableAlias() . '.clientId',
            ProviderInstance::tableAlias() . '.clientSecret',
            ProviderInstance::tableAlias() . '.settings'
        ];

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->environment === null) {
            $this->environment = Patron::getInstance()->getSettings()->getEnvironment();
        }
    }

    /**
     * @inheritdoc
     */
    public function populate($rows)
    {
        $results = parent::populate($rows);

        if (Patron::getInstance()->getSettings()->encryptStorageData === true) {
            foreach ($results as $key => $result) {
                $results[$key] = $this->parseResult($result, false);
            }
        }

        return $results;
    }

    /**
     * Decrypt
     * @inheritdoc
     */
    public function one($db = null)
    {
        return $this->parseResult(parent::one($db));
    }

    /**
     * @param $result
     * @return mixed
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    protected function parseResult($result, bool $checkSettings = true)
    {
        if (!is_array($result)) {
            return $result;
        }

        // Extract 'clientSecret'
        $clientSecret = ArrayHelper::remove($result, 'clientSecret');

        if (!empty($clientSecret)) {
            $result['clientSecret'] = ProviderHelper::decryptClientSecret($clientSecret, $checkSettings);
        }

        return $result;
    }


    /*******************************************
     * FIXED ORDER
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function fixedOrderColumn(): string
    {
        return 'id';
    }


    /*******************************************
     * PREPARE
     *******************************************/

    /**
     * @inheritdoc
     *
     * @throws QueryAbortedException if it can be determined that there won’t be any results
     */
    public function prepare($builder)
    {
        $this->applyConditions();
        $this->applyAuditAttributeConditions();
        $this->applyOrderByParams($builder->db);

        return parent::prepare($builder);
    }
}
