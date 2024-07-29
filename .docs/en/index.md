# Quick start

This extension adds support for user access handling for PSR-7 applications. The access check is done with help of the [Casbin](https://casbin.org) library

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
        middleware: false
        doctrine:
            mapping: false
            models: false
        casbin:
            database: false
        nette:
            application: false
    application:
        signInUrl:
        homeUrl: /
    services:
        identity: true
    casbin:
        model: rbac.model.conf
        policy:
```

Where:

- `token -> issuer` is string representation of your organisation or application and will be inserted into generated
  token
- `token -> signature` is secret string used for hashing tokens


- `enable -> middleware` enable or disable extension middlewares
- `enable -> doctrine -> mapping` enable or disable Doctrine2 owner field mapping to your entities
- `enable -> doctrine -> models` enable or disable Doctrine2 models services for reading, creating, updating and deleting tokens
- `enable -> casbin -> database` enable or disable Casbin database storage for policies
- `enable -> nette -> application` enable or disable [Nette](https://www.nette.org) access checker for presenters

- `application -> signInUrl` is route definition where user will be redirected when is not signed in
- `application -> homeUrl` is route definition where user will be redirected when signed in and open page for not signed in users

- `services -> identity` enable or disable simple identity factory

- `casbin -> model` path to Casbin model configuration. By default, the RBAC model is used
- `casbin -> policy` path to Casbin policy CSV file if the database storage for policies is not used

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

    /** @var Models\Tokens\Manager */
    private Models\Tokens\Manager $tokensManager;

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
 * @Secured\User(loggedIn)
 * @Secured\Role(manager,administrator)
 */
class RestrictedController
{

    /**
     * @Secured\User(loggedIn)
     * @Secured\Role(administrator)
     */
    public function readContent()
    {
        // ...
    }

    /**
     * @Secured\Resource(NameOfResource)
     * @Secured\Privilege(NameOfPrivilege)
     */
    public function readContent()
    {
        // ...
    }

    /**
     * @Secured\Permission(NameOfResource: NameOfPrivilege)
     */
    public function readContent()
    {
        // ...
    }

}
```

Restriction is configured with attributes:

`@Secured\User(loggedIn)` - user have to be signed in

`@Secured\User(guest)` - user have to be anonymous

`@Secured\Role(roleName)` - User have to be a member of provided user role. Role could be single or multiple separated with commas

`@Secured\Resource(resourceName)` - User have to have access to provided resource. Only one resource per check is allowed 

`@Secured\Privilege(privilegeName)` - This annotation is expecting permission string NameOfResource: NameOfPrivilege. This annotation allows multiple definitions 

`@Secured\Permission(permissionName)` - This  

Annotation could be combined or only on signle place. You could restrict whole controller or only specific methods.

# Using in presenters, components, models, etc.

Everywhere you want to check user rights to some action, you just create a simple call:

```php
$user->isAllowed('resource', 'privilege', ....);
```

and if user has access to this combination, you will receive **TRUE** value

## Using in Latte

In latte you can use two special macros.

```latte
<div class="some class">
    <p>
        This text is for everyone....
    </p>
    {ifAllowed resource => 'system', privilege => 'manage'}
    <p>
        But this one is only for special persons....
    </p>
    {else}
    <p>
        And here is a content for non special persons....
    </p>
    {/ifAllowed}
</div>
```

Macro **ifAllowed** is very similar to annotations definition. You can use here one or all of available parameters: user, resource, privilege, permission or role.

This macro can be also used as **n:macro**:

```latte
<div class="some class">
    <p>
        This text is for everyone....
    </p>
    <p n:ifAllowed resource => 'system', privilege => 'manage user permissions'>
        But this one is only for special persons....
    </p>
    <p n:elseAllowed>
        And here is a content for non special persons....
    </p>
</div>
```

And second special macro is for links:

```latte
<a n:allowedHref="Settings:" class="some class">
    Link text...
</a>

Macro **n:allowedHref** is expecting only valid link and in case user doesn't have access to this link, link is not clickable.

```
# Tip

We recommend using this extension with [fastybird/web-server](https://github.com/FastyBird/web-server) package. This
package will solve for you whole php server setup and will fully cooperate with this security extension.

***
Homepage [https://www.fastybird.com](https://www.fastybird.com) and
repository [https://github.com/FastyBird/simple-auth](https://github.com/FastyBird/simple-auth).
