# ray/csrf

CSRF protection for Ray.Di / BEAR.Resource applications.

`ray/csrf` keeps CSRF outside the Resource semantic contract: Resource method
parameters do not need a CSRF token, and request schemas / API documentation do
not need to expose it. Browser-facing unsafe methods are protected by Ray AOP
interceptors.

## Installation

```bash
composer require ray/csrf
```

## Module

Install `CsrfModule` in your application module:

```php
use Ray\Csrf\CsrfModule;

$this->install(new CsrfModule(
    allowedOrigin: 'https://example.com',
    tokenField: '_csrf_token',
));
```

`tokenField` defaults to `_csrf_token`. Applications with an existing wire name
can override it, for example BeMart uses `csrfToken`:

```php
$this->install(new CsrfModule(tokenField: 'csrfToken'));
```

## Resource attributes

```php
use Ray\Csrf\Attribute\CsrfToken;
use Ray\Csrf\Attribute\SameOrigin;

final class Article extends ResourceObject
{
    #[SameOrigin]
    #[CsrfToken]
    public function onPost(string $title, string $body): static
    {
        // No csrfToken parameter: CSRF is a transport concern, not a
        // Resource semantic argument.
        return $this;
    }
}
```

`#[SameOrigin]` validates browser origin signals (`Sec-Fetch-Site`, `Origin`,
`Referer`). If `allowedOrigin` is `null`, only this origin gate is skipped.

When `Sec-Fetch-Site` is present (all modern browsers send it), the request is
judged by that browser-computed signal alone and `allowedOrigin` is not
compared. `allowedOrigin` is consulted only for the `Origin` / `Referer`
fallback used by older or non-browser clients.

`#[CsrfToken]` validates a synchroniser token and is not disabled by
`allowedOrigin: null`. Tests and fake contexts should override
`CsrfTokenInterface` when they do not drive real HTTP form submissions.

## Token sources

The submitted token is read in this order:

1. `X-CSRF-Token` header
2. `ResourceObject->uri->query[$field]`
3. `$_POST[$field]`

This keeps the token out of Resource method arguments while still supporting
BEAR.Resource requests, HTML forms, and JavaScript submissions.

The header name `X-CSRF-Token` is fixed and is **not** affected by `tokenField`;
only the query and `$_POST` sources use the configured `tokenField` name.

## Template example

Issue a token through `CsrfTokenInterface` in your renderer or template helper
and render it as a hidden field:

```html
<input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
```

The Resource does not need to know that this field exists.
