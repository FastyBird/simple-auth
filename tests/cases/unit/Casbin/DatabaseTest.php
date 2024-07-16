<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit\Casbin;

use Casbin;
use FastyBird\SimpleAuth\Models;
use FastyBird\SimpleAuth\Queries;
use FastyBird\SimpleAuth\Tests\Cases\Unit\DbTestCase;
use FastyBird\SimpleAuth\Tests\Fixtures;
use FastyBird\SimpleAuth\Types;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use Nette\DI;
use Nette\Utils;
use Ramsey\Uuid;

final class DatabaseTest extends DbTestCase
{

	public function setUp(): void
	{
		$this->registerNeonConfigurationFile(__DIR__ . '/database.neon');

		parent::setUp();
	}

	/**
	 * @throws Casbin\Exceptions\CasbinException
	 * @throws DI\MissingServiceException
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 */
	public function testPolicy(): void
	{
		$manager = $this->container->getByType(Models\Policies\Manager::class);

		$repository = $this->container->getByType(Models\Policies\Repository::class);

		$parentId = Uuid\Uuid::fromString('ff11f4fd-c06b-40a2-9a79-6dd3e3a10373');

		$enforcer = $this->container->getByType(Casbin\Enforcer::class);

		self::assertSame(
			['visitor', 'administrator'],
			$enforcer->getAllRoles(),
		);

		self::assertFalse($enforcer->enforce('2784d750-f085-4580-8525-4d622face83d', 'data2', 'read'));
		self::assertTrue($enforcer->enforce('2784d750-f085-4580-8525-4d622face83d', 'data3', 'read'));

		$createdPolicy = $manager->create(Utils\ArrayHash::from([
			'entity' => Fixtures\Entities\TestPolicyEntity::class,
			'parent' => $parentId,
			'type' => Types\PolicyType::POLICY,
			'v0' => '2784d750-f085-4580-8525-4d622face83d',
			'v1' => 'data2',
			'v2' => 'read',
		]));

		$findPolicy = new Queries\FindPolicies();
		$findPolicy->byId($createdPolicy->getId());

		$foundPolicy = $repository->findOneBy($findPolicy);

		self::assertNotNull($foundPolicy);

		self::assertTrue($enforcer->enforce('2784d750-f085-4580-8525-4d622face83d', 'data2', 'read'));

		$enforcer->deletePermissionForUser('2784d750-f085-4580-8525-4d622face83d', 'data2', 'read');

		$findPolicy = new Queries\FindPolicies();
		$findPolicy->byId($createdPolicy->getId());

		$foundPolicy = $repository->findOneBy($findPolicy);

		self::assertNull($foundPolicy);

		self::assertFalse($enforcer->enforce('2784d750-f085-4580-8525-4d622face83d', 'data2', 'read'));
	}

}
