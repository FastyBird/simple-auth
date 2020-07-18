<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\NodeAuth\Mapping;
use FastyBird\NodeAuth\Middleware;
use FastyBird\NodeAuth\Security;
use FastyBird\NodeAuth\Subscribers;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

final class ServicesTest extends BaseTestCase
{

	public function testServicesRegistration(): void
	{
		$container = $this->createContainer();

		Assert::notNull($container->getByType(Middleware\UserMiddleware::class));
		Assert::notNull($container->getByType(Middleware\Route\AccessMiddleware::class));

		Assert::notNull($container->getByType(Subscribers\UserSubscriber::class));

		Assert::notNull($container->getByType(Mapping\Driver\Owner::class));

		Assert::notNull($container->getByType(Security\TokenBuilder::class));
		Assert::notNull($container->getByType(Security\TokenReader::class));
		Assert::notNull($container->getByType(Security\TokenValidator::class));
		Assert::notNull($container->getByType(Security\IdentityFactory::class));
	}

}

$test_case = new ServicesTest();
$test_case->run();
