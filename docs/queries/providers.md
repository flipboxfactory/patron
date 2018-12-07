# Providers

Retrieve [Providers] from storage.

[[toc]]

### `clientId( $value )`

Returns self; the [Provider Query]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$value`     | [string], [string\[\]], [null] | A [Provider] Client Id

::: code
```twig
{% set provider = craft.patron.providers.clientId('abcdefg').one() %}
```

```php
use flipbox/patron/queries/ProviderQuery;

$provider = ProviderQuery::find()
    ->clientId('abcdefg')
    ->one();
```
:::

### `id( $value )`

Returns self; the [Provider Query]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$value`     | [integer], [integer\[\]], [string], [string\[\]], [null] | A [Provider] database id

::: code
```twig
{% set provider = craft.patron.providers.id(1).one() %}
```

```php
use flipbox/patron/queries/ProviderQuery;

$provider = ProviderQuery::find()
    ->id(1)
    ->one();
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