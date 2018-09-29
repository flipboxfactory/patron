# Providers

This service is used to retrieve one or many [Providers].

[[toc]]

### `find( $identifier, int $siteId = null )`

Returns an [Provider]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$identifier`     | [string], [integer], [Provider] | A unique [Provider] identifier
| `$siteId`         | [integer], [null]         | The [Site] Id that the [Provider] must belong to

::: code
```twig
{% set element = craft.patron.tokens.find(1) %}
```

```php
use flipbox\patron\Patron;

$element = Patron::getInstance()->getProviders()->find(1);
```
:::

### `getQuery( $criteria )`

Returns a [Provider Query].

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | An array of [Provider Query] criteria.


::: code
```twig
{% set query = craft.patron.tokens.getQuery({
    id: 1
}) %}
```

```php
use flipbox\patron\Patron;

$element = Patron::getInstance()->getProviders()->getQuery([
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

[Provider Query]: ../queries/provider.md "Provider Query"
[Providers]: ../objects/provider.md "Provider"
[Provider]: ../objects/provider.md "Provider"