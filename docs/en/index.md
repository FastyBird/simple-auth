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

This extension is dependent on other extensions, and they have to be registered too

```neon
extensions:
    ....
    ipubDoctrineCrud : IPub\DoctrineCrud\DI\DoctrineCrudExtension
    ipubDoctrineConsistence : IPub\DoctrineConsistence\DI\DoctrineConsistenceExtension
    ipubDynamicDiscriminatorMap : IPub\DoctrineDynamicDiscriminatorMap\DI\DoctrineDynamicDiscriminatorMapExtension
    fbDateTimeFactory : FastyBird\DateTimeFactory\DI\DateTimeFactoryExtension
```

> For information how to configure this extensions please visit their doc pages

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

## Create access token entity

You could simply create you own entity by extending `Token` entity class 

```php
namespace Your\CoolApp\Entities;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\SimpleAuth\Entities;

/**
 * @ORM\Entity
 */
class AccessTokenEntity extends Entities\Tokens\Token
{

    // Your additional attributes could be here...

}
```

And now this entity have to registered with you Doctrine2 implementation.

## Sign in with user credentials

In your controller you could use `User` service and process sign in a token generation:

```php
namespace Your\CoolApp\Controllers;

use FastyBird\SimpleAuth\Models;
use FastyBird\SimpleAuth\Security;
use FastyBird\SimpleAuth\Types;
use Nette\Utils;
use Ramsey\Uuid;
use Throwable;

class SomeSessionController {

    /** @var Security\User */
    private Security\User $user;

    /** @var Models\Tokens\ITokensManager */
    private Models\Tokens\ITokensManager $tokensManager;

    /** @var Security\TokenBuilder */
    private Security\TokenBuilder $tokenBuilder;

    public handleAction(string $username, string $password): Response
    {
        try {
            $identity = $this->user->login($username, $password);

        } catch (Throwable $ex) {
            // Handle invalid credentials etd.

            return Response('401', 'Invalid credentials');
        }

        $values = Utils\ArrayHash::from([
            'id'        => Uuid\Uuid::uuid4(),
            'entity'    => Your\CoolApp\Entities\AccessTokenEntity::class,
            'token'     => $this->tokenBuilder->build($identity->getId(), $identity->getRoles()),
        ]);

        $accessToken = $this->tokensManager->create($values);

        return Response(200, [
            'token' => $accessToken->getToken(),
            'type'  => 'bearer'
        ]);
    }

}
```

If you have you own entities manages system, you could implement you own entity creation process. All what you need to use is `$tokensManager` for JWT token generation.

## Restrict access to resources

Access to controllers could be managed via PHPDoc annotation:

```php
namespace Your\CoolApp\Controllers;

/**
 * @Secured(loggedIn)
 * @Secured\Role(manager)
 */
class RestrictedController {

}
```

Restriction is configured with attributes:

`@Secured(loggedIn)` - user have to have valid access token, eg. user have to be logged-in

`@Secured(guest)` - user have to be anonymous

`@Secured\Role(roleName)` - Access middleware is going to check if logged-in user has configured role. Role could be single or multiple separated with commas
