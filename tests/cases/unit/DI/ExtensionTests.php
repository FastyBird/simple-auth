<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit\DI;

use Casbin;
use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Mapping;
use FastyBird\SimpleAuth\Middleware;
use FastyBird\SimpleAuth\Security;
use FastyBird\SimpleAuth\Subscribers;
use FastyBird\SimpleAuth\Tests\Cases\Unit\BaseTestCase;
use Nette\DI;

final class ExtensionTests extends BaseTestCase
{

	/**
	 * @throws DI\MissingServiceException
	 */
	public function testServicesRegistration(): void
	{
		$container = $this->createContainer();

		self::assertNotNull($container->getByType(SimpleAuth\Auth::class, false));
		self::assertNotNull($container->getByType(SimpleAuth\Configuration::class, false));

		self::assertNotNull($container->getByType(Middleware\User::class, false));
		self::assertNotNull($container->getByType(Middleware\Access::class, false));

		self::assertNotNull($container->getByType(Subscribers\Application::class, false));
		self::assertNotNull($container->getByType(Subscribers\User::class, false));

		self::assertNotNull($container->getByType(Mapping\Driver\Owner::class, false));

		self::assertNotNull($container->getByType(Security\TokenBuilder::class, false));
		self::assertNotNull($container->getByType(Security\TokenReader::class, false));
		self::assertNotNull($container->getByType(Security\TokenValidator::class, false));
		self::assertNotNull($container->getByType(Security\IdentityFactory::class, false));
		self::assertNotNull($container->getByType(Security\User::class, false));
		self::assertNotNull($container->getByType(Security\UserStorage::class, false));

		self::assertNotNull($container->getByType(Casbin\Enforcer::class, false));
	}

}
