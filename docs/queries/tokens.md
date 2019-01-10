# Tokens

Retrieve [Tokens] from storage.

[[toc]]

### `clientId( $value )`

Returns self; the [Token Query]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$value`     | [string], [string\[\]], [null] | A [Token] Client Id

::: code
```twig
{% set token = craft.patron.tokens.clientId('abcdefg').one() %}
```

```php
use flipbox/patron/queries/TokenQuery;

$token = TokenQuery::find()
    ->clientId('abcdefg')
    ->one();
```
:::

### `id( $value )`

Returns self; the [Token Query]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$value`     | [integer], [integer\[\]], [string], [string\[\]], [null] | A [Token] database id

::: code
```twig
{% set token = craft.patron.tokens.id(1).one() %}
```

```php
use flipbox/patron/queries/TokenQuery;

$token = TokenQuery::find()
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

[Token Query]: ../queries/token.md "Token Query"
[Tokens]: ../objects/token.md "Token"
[Token]: ../objects/token.md "Token"