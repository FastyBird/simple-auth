# Quick start

This extension adds support for user access handling for PSR-7 applications.

***

## Installation

The best way to install **fastybird/simple-auth** is using [Composer](http://getcomposer.org/):

```sh
composer require fastybird/simple-auth
```

After that, you have to register extension in *config.neon*.

```neon
extensions:
    fbSimpleAuth: FastyBird\SimpleAuth\DI\SimpleAuthExtension
```

This extension is dependent on other extensions, and they have to be registered too

```neon
extensions:
    ....
    ipubDoctrineCrud: IPub\DoctrineCrud\DI\DoctrineCrudExtension
    ipubDoctrineConsistence: IPub\DoctrineConsistence\DI\DoctrineConsistenceExtension
    ipubDynamicDiscriminatorMap: IPub\DoctrineDynamicDiscriminatorMap\DI\DoctrineDynamicDiscriminatorMapExtension
    fbDateTimeFactory: FastyBird\DateTimeFactory\DI\DateTimeFactoryExtension
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

- `token -> issuer` is string representation of your organisation or application and will be inserted into generated
  token
- `token -> signature` is secret string used for hashing tokens


- `enable -> middleware` enable or disable extension middlewares
- `enable -> doctrine -> mapping` enable or disable Doctrine2 owner field mapping to your entities
- `enable -> doctrine -> models` enable or disable Doctrine2 models services for reading, creating, updating and
  deleting tokens


- `services -> identity` enable or disable simple identity factory

## Application user & identity

Everything is about user and its identity. This extension is registering service `FastyBirdy\SimpleAuth\Security\User`
which handle state of user: logged-in or guest and this service could be used to determine user state and get user
identity.

User service need your implementation of Authenticator, so you have to create your own authenticator service:

```php
namespace Your\CoolApp\Security;

use FastyBird\SimpleAuth\Security as SimpleAuthSecurity;

class Authenticator implements SimpleAuthSecurity\IAuthenticator
{

    public function authenticate(array $credentials): SimpleAuthSecurity\IIdentity
    {
        // User login logic here...
        
        return $identity;
    }

}
```

and now register this authenticator in your neon configuration and extension will inject it to the user service:

```neon
services:
    - {type: Your\CoolApp\Security\Authenticator}
```

> Built-in user service could be extended with you own implementation. All what you have to do is to extend base use class `FastyBirdy\SimpleAuth\Security\User` and register it as a service

### User identity

This extension has built-in plain identity and identity factory. Identity
factory `FastyBird\SimpleAuth\Security\IdentityFactory` will extract claims from JWT token and create plain
identity `FastyBird\SimpleAuth\Security\PlainIdentity`

> If you want your own implementation of Identity and identity factory you could disable built-in solution via config and implement it your way.

## Access token entity

Token is used for user authorization. This extension has base token entity structure and you have to extend it with you
specific implementation. You could simply create you own access token entity by
extending `FastyBird\SimpleAuth\Entities\Token` entity class

```php
namespace Your\CoolApp\Entities;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\SimpleAuth\Entities;

/**
 * @ORM\Entity
 */
class AccessTokenEntity extends Entities\Tokens\Token
{

    /**
     * @var Entities\UserEntity
     *
     * @ORM\ManyToOne(targetEntity="Your\CoolApp\Entities\UserEntity")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="cascade", nullable=false)
     */
    private Entities\UserEntity $user;

    // Your additional attributes could be here...

}
```

And now this entity have to be registered with you Doctrine2 implementation.

## Create access token for user - user authentication

In your controller you could use `FastyBird\SimpleAuth\Security\User` service for obtaining user identity and create and
return user granted token:

```php
namespace Your\CoolApp\Controllers;

use FastyBird\SimpleAuth\Models;
use FastyBird\SimpleAuth\Security;
use Nette\Utils;
use Ramsey\Uuid;
use Throwable;

class SomeSessionController {

    /** @var Security\User */
    private Security\User $user;

    /** @var Models\Tokens\TokensManager */
    private Models\Tokens\TokensManager $tokensManager;

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

> If you have you own entities management system, you could implement you own entity creation process and replace `$tokenManager` implementation.

Important part of this example is:

```php
$this->tokenBuilder->build($userId, $rolesArray, $expiration)
```

Where:

- `$userdId` - is user identifier
- `$rolesArray` - is an array of roles names or identifiers, it has to be a scalar array
- `$expiration` - is optional parameter which accept `\DateTime` object which is defining expiration date of the
  generated token. After this date token will be invalid and can no longer be used for authorization

## User authorization

This extension has two middleware. One for user token validation and second for user authorization.

### User token validation

Middleware `FastyBird\SimpleAuth\Middleware\UserMiddleware` will take incoming request and try to find a specific
header `authorization` where should be placed user acess token.

This token is validated with your token secret signature and if is valid, application user instance is created.

### User authorization

The second middleware `FastyBird\SimpleAuth\Middleware\AccessMiddleware` is here to check user if has enough access
right to access requested resources.

This middleware is using [ipub/slim-router](https://github.com/ipublikuj/slim-router) package. It check incoming request
if it has route object in attribute and if is present, try to check configured permissions with logged-in user.

The core of this middleware is in annotation checker service `FastyBird\SimpleAuth\Security\AnnotationChecker`. This
service take controller class as a second parameter and controller method as third parameter. It tries to read security
annotations and decide if user has or has not access to resources.

## Restrict access to resources

Access to controllers or methods are managed via PHPDoc annotation:

```php
namespace Your\CoolApp\Controllers;

/**
 * @Secured(loggedIn)
 * @Secured\Role(manager,administrator)
 */
class RestrictedController
{

    /**
     * @Secured(loggedIn)
     * @Secured\Role(administrator)
     */
    public function readContent()
    {
        // ...
    }

}
```

Restriction is configured with attributes:

`@Secured(loggedIn)` - user have to have valid access token, eg. user have to be logged-in

`@Secured(guest)` - user have to be anonymous

`@Secured\Role(roleName)` - Access middleware is going to check if logged-in user has configured role. Role could be
single or multiple separated with commas

Annotation could be combined or only on signle place. You could restrict whole controller or only specific methods.

# Tip

We recommend using this extension with [fastybird/web-server](https://github.com/FastyBird/web-server) package. This
package will solve for you whole php server setup and will fully cooperate with this security extension.

***
Homepage [https://www.fastybird.com](https://www.fastybird.com) and
repository [https://github.com/FastyBird/simple-auth](https://github.com/FastyBird/simple-auth).
