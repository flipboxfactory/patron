# Manage Providers

This service is used to manage one or many [Provider Records].

[[toc]]

### `find( $identifier, int $siteId = null )`

Returns an [Provider Record]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$identifier`     | [string], [integer], [Provider Record] | A unique [Provider Record] identifier
| `$siteId`         | [integer], [null]         | The [Site] Id that the [Provider Record] must belong to

::: code
```twig
{% set element = craft.patron.manageProviders.find(1) %}
{% set element = craft.patron.manageProviders.find('flipbox') %}

```

```php
use flipbox\patron\Patron;

$element = Patron::getInstance()->getManageProviders()->find(1);
$element = Patron::getInstance()->getManageProviders()->find('flipbox');
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

$element = Patron::getInstance()->getManageProviders()->getQuery([
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