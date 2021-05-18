# Quick start

This extension adds support for user access handling for PSR-7 applications.

## Installation

The best way to install **fastybird/simple-auth** is using [Composer](http://getcomposer.org/):

```sh
composer require fastybird/simple-auth
```

After that you have to register extension in *config.neon*.

```neon
extensions:
    fbSimpleAuth: FastyBird\SimpleAuth\DI\SimpleAuthExtension
```

## Configuration

This extension has some configuration options:

```neon
fbSimpleAuth:
    token:
        issuer: fb_tester
        signature: someSecurityHasToProtectGeneratedToken
    enable:
        middleware: true
        doctrine:
            mapping: true
            models: true
    services:
        identity: true
```

Where:

- `token->issuer` is string representation of your organisation or application and will be inserted into generated token
- `token->signature` is secret string used for hashing tokens
- `enable->middleware` enable or disable extension middlewares
- `enable->doctrine->mapping` enable or disable Doctrine2 owner field mapping to your entities
- `enable->doctrine->models` enable or disable Doctrine2 models services for reading, creating, updating and deleting tokens
- `services->identity` enable or disable simple identity factory
