# symfony-mail-ds

Version: 2.2.10

Design-system screens for symfony-mail: the development mailbox

## Table of Contents

- [Installation](#installation)

## Installation

```php
// config/bundles.php
Wexample\SymfonyMailDs\WexampleSymfonyMailDsBundle::class => ['all' => true],
```

With `MAILER_DSN=mailbox://default` (see symfony-mail), the page `/mailbox/` lists the mails the application sent, newest first. It is routed in `dev` and `test` only, and asks for no role: a sign-in link or code is read there before signing in. An application that guards every path opens this one in its `access_control` — in the main list: Symfony refuses `access_control` under `when@dev`, and outside dev and test the path leads nowhere. Quoted, for the braces of the pattern:

```yaml
security:
    access_control:
        - { path: '^/([a-z]{2}(_[A-Z]{2})?/)?mailbox/', roles: PUBLIC_ACCESS }
```

A mail's HTML is served by its own route under a `Content-Security-Policy: sandbox` header and shown in an iframe: it neither wears the application's styles nor runs in its origin.

In a menu: `menu_item_collapsible_from_controller(render_pass, 'Wexample\\SymfonyMailDs\\Controller\\Pages')`. Outside dev and test, the route does not exist and the entry is not drawn.
