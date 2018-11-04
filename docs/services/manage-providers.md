# Manage Providers

This service is used to manage one or many [Provider Records] in Craft.

[[toc]]

### `find( $identifier )`

Returns an [Provider Record]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$identifier`     | [string], [integer], [Provider Record] | A unique [Provider Record] identifier

::: code

```twig
{% set provider = craft.patron.manageProviders.find(1) %}
{% set provider = craft.patron.manageProviders.find('flipbox') %}

```

```php
use flipbox\patron\Patron;

$provider = Patron::getInstance()->getManageProviders()->find(1);
$provider = Patron::getInstance()->getManageProviders()->find('flipbox');
```
:::

### `getQuery( $criteria )`

Returns a [Provider Query].

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | An array of [Provider Query] criteria.


::: code
```twig
{% set query = craft.patron.manageProviders.getQuery({
    id: 1
}) %}
```

```php
use flipbox\patron\Patron;

$provider = Patron::getInstance()->getManageProviders()->getQuery([
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

[Provider Query]: ../queries/token.md "Provider Query"
[Provider Records]: ../objects/token-record.md "Provider Records"
[Provider Record]: ../objects/token-record.md "Provider Record"