# Manage Tokens

This service is used to manage one or many [Token Records] in Craft.

[[toc]]

### `find( $identifier )`

Returns an [Token Record]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$identifier`     | [string], [integer], [Token Record] | A unique [Token Record] identifier

::: code
```twig
{% set token = craft.patron.manageTokens.find(1) %}
```

```php
use flipbox\patron\Patron;

$token = Patron::getInstance()->getManageTokens()->find(1);
```
:::

### `getQuery( $criteria )`

Returns a [Token Query].

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | An array of [Token Query] criteria.


::: code
```twig
{% set query = craft.patron.manageTokens.getQuery({
    id: 1
}) %}
```

```php
use flipbox\patron\Patron;

$token = Patron::getInstance()->getManageTokens()->getQuery([
    'id' => 1
]);
```
:::


[integer]: http://www.php.net/language.types.integer
[integer\[\]]: http://www.php.net/language.types.integer
[array]: http://www.php.net/language.types.array
[string]: http://www.php.net/language.types.string
[string\[\]]: http://www.php.net/language.types.string
[null]: http://www.php.net/language.types.null

[Site]: https://docs.craftcms.com/api/v3/craft-models-site.html

[Token Query]: ../queries/token.md "Token Query"
[Token Records]: ../objects/token-record.md "Token Records"
[Token Record]: ../objects/token-record.md "Token Record"