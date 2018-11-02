# Tokens

This service is used to retrieve one or many [Tokens].

[[toc]]

### `find( $identifier )`

Returns an [Token]

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$identifier`     | [string], [integer], [Token] | A unique [Token] identifier

::: code
```twig
{% set token = craft.patron.tokens.find(1) %}
```

```php
use flipbox\patron\Patron;

$token = Patron::getInstance()->getTokens()->find(1);
```
:::

### `getQuery( $criteria )`

Returns a [Token Query].

| Argument          | Accepts                   | Description
| ----------        | ----------                | ----------
| `$criteria`       | [array]                   | An array of [Token Query] criteria.


::: code
```twig
{% set query = craft.patron.tokens.getQuery({
    id: 1
}) %}
```

```php
use flipbox\patron\Patron;

$query = Patron::getInstance()->getTokens()->getQuery([
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
[Tokens]: ../objects/token.md "Token"
[Token]: ../objects/token.md "Token"