<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/patron/license
 * @link       https://www.flipboxfactory.com/software/patron/
 */

namespace flipbox\patron\validators;

use Craft;
use craft\helpers\StringHelper;
use yii\validators\Validator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class TokenValidator extends Validator
{

    /**
     * @var array
     */
    public static $baseReservedWords = [
        'token',
        'id'
    ];

    /**
     * @var string
     */
    public static $identifierPattern = '[a-zA-Z0-9-_\*]*';

    /**
     * @var array
     */
    public $reservedWords = [];

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $identifier = $model->$attribute;

        // Handles are always required, so if it's blank, the required validator will catch this.
        if ($identifier) {
            $reservedWords = array_merge($this->reservedWords, static::$baseReservedWords);
            $reservedWords = array_map([StringHelper::class, 'toLowerCase'], $reservedWords);
            $lcHandle = StringHelper::toLowerCase($identifier);

            if (in_array($lcHandle, $reservedWords, true)) {
                $message = Craft::t(
                    'app',
                    '“{identifier}” is a reserved word.',
                    ['identifier' => $identifier]
                );
                $this->addError($model, $attribute, $message);
            } else {
                if (!preg_match('/^' . static::$identifierPattern . '$/', $identifier)) {
                    $altMessage = Craft::t(
                        'app',
                        '“{identifier}” isn’t a valid identifier.',
                        ['identifier' => $identifier]
                    );
                    $message = $this->message ?? $altMessage;
                    $this->addError($model, $attribute, $message);
                }
            }
        }
    }
}
