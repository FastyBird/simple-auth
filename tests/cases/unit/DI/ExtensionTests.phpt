<?php declare(strict_types = 1);

namespace Tests\Cases\Unit;

use FastyBird\SimpleAuth\Mapping;
use FastyBird\SimpleAuth\Middleware;
use FastyBird\SimpleAuth\Security;
use FastyBird\SimpleAuth\Subscribers;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

/**
 * @testCase
 */
final class ExtensionTests extends BaseTestCase
{

	public function testServicesRegistration(): void
	{
		$container = $this->createContainer();

		Assert::notNull($container->getByType(Middleware\User::class));
		Assert::notNull($container->getByType(Middleware\Access::class));

		Assert::notNull($container->getByType(Subscribers\User::class));

		Assert::notNull($container->getByType(Mapping\Driver\Owner::class));

		Assert::notNull($container->getByType(Security\TokenBuilder::class));
		Assert::notNull($container->getByType(Security\TokenReader::class));
		Assert::notNull($container->getByType(Security\TokenValidator::class));
		Assert::notNull($container->getByType(Security\IdentityFactory::class));
		Assert::notNull($container->getByType(Security\User::class));
		Assert::notNull($container->getByType(Security\UserStorage::class));
	}

}

$test_case = new ExtensionTests();
$test_case->run();
