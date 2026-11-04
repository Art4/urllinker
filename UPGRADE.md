# Upgrade guide

## From 2.2 to 2.3

Nothing breaks. 2.3 deprecates two things that the next major version removes.

### The legacy namespace `Youthweb\UrlLinker`

The public classes live in the namespace `Art4\UrlLinker`. The old names are aliases that stay silent: they trigger no notice, so nothing warns you. They are removed in 3.0.

| Before | After |
|---|---|
| `Youthweb\UrlLinker\UrlLinker` | `Art4\UrlLinker\UrlLinker` |
| `Youthweb\UrlLinker\UrlLinkerInterface` | `Art4\UrlLinker\UrlLinkerInterface` |
| `Youthweb\UrlLinker\DomainStorage` | `Art4\UrlLinker\DomainStorage` |

The Composer package name `youthweb/urllinker` does not change.

Find the remaining uses:

```sh
grep -rn 'Youthweb\\UrlLinker' src
```

Replace them with the canonical namespace by hand, or with [Rector](https://getrector.com):

```php
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;

return RectorConfig::configure()
    ->withConfiguredRule(RenameClassRector::class, [
        'Youthweb\\UrlLinker\\UrlLinker' => 'Art4\\UrlLinker\\UrlLinker',
        'Youthweb\\UrlLinker\\UrlLinkerInterface' => 'Art4\\UrlLinker\\UrlLinkerInterface',
        'Youthweb\\UrlLinker\\DomainStorage' => 'Art4\\UrlLinker\\DomainStorage',
    ]);
```

Instances report the canonical name: `get_class()` and `serialize()` return `Art4\UrlLinker\...` even for an object created through a legacy name.

### The config option `cutUrlsAtEntities`

Do not set it. The default already links URLs across character references that stand for URL characters (for example `&amp;`). Setting it to `true` triggers an `E_USER_DEPRECATED` notice.
