<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit\Casbin;

use Casbin;
use Doctrine\DBAL;
use FastyBird\SimpleAuth\Entities;
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Models;
use FastyBird\SimpleAuth\Queries;
use FastyBird\SimpleAuth\Security;
use FastyBird\SimpleAuth\Tests\Cases\Unit\DbTestCase;
use FastyBird\SimpleAuth\Tests\Fixtures;
use FastyBird\SimpleAuth\Types;
use IPub\DoctrineCrud\Exceptions as DoctrineCrudExceptions;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use Nette\DI;
use Nette\Utils;
use Ramsey\Uuid;
use function array_map;
use function array_merge;

final class DatabaseTest extends DbTestCase
{

	public function setUp(): void
	{
		$this->registerNeonConfigurationFile(__DIR__ . '/database.neon');

		parent::setUp();
	}

	/**
	 * @throws Casbin\Exceptions\CasbinException
	 * @throws DBAL\Exception\UniqueConstraintViolationException
	 * @throws DI\MissingServiceException
	 * @throws DoctrineCrudExceptions\EntityCreation
	 * @throws DoctrineCrudExceptions\InvalidArgument
	 * @throws DoctrineCrudExceptions\InvalidState
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 * @throws Exceptions\InvalidState
	 */
	public function testPolicy(): void
	{
		$manager = $this->container->getByType(Models\Policies\Manager::class);

		$repository = $this->container->getByType(Models\Policies\Repository::class);

		$parentId = Uuid\Uuid::fromString('ff11f4fd-c06b-40a2-9a79-6dd3e3a10373');

		$enforcerFactory = $this->container->getByType(Security\EnforcerFactory::class);

		$enforcer = $enforcerFactory->getEnforcer();

		self::assertSame(
			['visitor', 'administrator'],
			$enforcer->getAllRoles(),
		);

		self::assertTrue(
			$enforcer->enforce('2784d750-f085-4580-8525-4d622face83d', 'data1', 'read'),
		);
		self::assertFalse(
			$enforcer->enforce('2784d750-f085-4580-8525-4d622face83d', 'data2', 'read'),
		);
		self::assertTrue(
			$enforcer->enforce('2784d750-f085-4580-8525-4d622face83d', 'data3', 'read'),
		);

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

		self::assertTrue(
			$enforcer->enforce('2784d750-f085-4580-8525-4d622face83d', 'data2', 'read'),
		);

		$manager->delete($createdPolicy);

		$findPolicy = new Queries\FindPolicies();
		$findPolicy->byId($createdPolicy->getId());

		$foundPolicy = $repository->findOneBy($findPolicy);

		self::assertNull($foundPolicy);

		$enforcer->loadPolicy();

		self::assertFalse(
			$enforcer->enforce('2784d750-f085-4580-8525-4d622face83d', 'data2', 'read'),
		);

		$enforcer->addRoleForUser('2784d750-f085-4580-8525-4d622face83d', 'new_role');

		self::assertEqualsCanonicalizing([
			'new_role',
			'visitor',
		], $enforcer->getRolesForUser('2784d750-f085-4580-8525-4d622face83d'));

		$findPolices = new Queries\FindPolicies();

		$policies = $repository->findAllBy($findPolices);

		self::assertCount(13, $policies);
		self::assertEqualsCanonicalizing([
			[
				'entity' => 'FastyBird\SimpleAuth\Tests\Fixtures\Entities\TestPolicyEntity',
				'type' => 'p',
				'v0' => 'administrator',
				'v1' => 'data2',
				'v2' => 'read',
				'v3' => null,
				'v4' => null,
				'v5' => null,
			],
			[
				'entity' => 'FastyBird\SimpleAuth\Tests\Fixtures\Entities\TestPolicyEntity',
				'type' => 'p',
				'v0' => 'administrator',
				'v1' => 'data2',
				'v2' => 'write',
				'v3' => null,
				'v4' => null,
				'v5' => null,
			],
			[
				'entity' => 'FastyBird\SimpleAuth\Tests\Fixtures\Entities\TestPolicyEntity',
				'type' => 'p',
				'v0' => '2784d750-f085-4580-8525-4d622face83d',
				'v1' => 'data1',
				'v2' => 'read',
				'v3' => null,
				'v4' => null,
				'v5' => null,
			],
			[
				'entity' => 'FastyBird\SimpleAuth\Tests\Fixtures\Entities\TestPolicyEntity',
				'type' => 'p',
				'v0' => 'administrator',
				'v1' => 'data3',
				'v2' => 'write',
				'v3' => null,
				'v4' => null,
				'v5' => null,
			],
			[
				'entity' => 'FastyBird\SimpleAuth\Entities\Policies\Policy',
				'type' => 'g',
				'v0' => '2784d750-f085-4580-8525-4d622face83d',
				'v1' => 'new_role',
				'v2' => null,
				'v3' => null,
				'v4' => null,
				'v5' => null,
			],
			[
				'entity' => 'FastyBird\SimpleAuth\Tests\Fixtures\Entities\TestRoleEntity',
				'type' => 'g',
				'v0' => '2784d750-f085-4580-8525-4d622face83d',
				'v1' => 'visitor',
				'v2' => null,
				'v3' => null,
				'v4' => null,
				'v5' => null,
			],
			[
				'entity' => 'FastyBird\SimpleAuth\Tests\Fixtures\Entities\TestPolicyEntity',
				'type' => 'p',
				'v0' => 'visitor',
				'v1' => 'data3',
				'v2' => 'read',
				'v3' => null,
				'v4' => null,
				'v5' => null,
			],
			[
				'entity' => 'FastyBird\SimpleAuth\Tests\Fixtures\Entities\TestRoleEntity',
				'type' => 'g',
				'v0' => '5785924c-75a8-42ae-9bdd-a6ce5edbadac',
				'v1' => 'administrator',
				'v2' => null,
				'v3' => null,
				'v4' => null,
				'v5' => null,
			],
			[
				'entity' => 'FastyBird\SimpleAuth\Tests\Fixtures\Entities\TestPolicyEntity',
				'type' => 'p',
				'v0' => 'administrator',
				'v1' => 'data3',
				'v2' => 'read',
				'v3' => null,
				'v4' => null,
				'v5' => null,
			],
			[
				'entity' => 'FastyBird\SimpleAuth\Tests\Fixtures\Entities\TestPolicyEntity',
				'type' => 'p',
				'v0' => 'c450531d-0f10-4587-a0ce-42fb48a8a8ad',
				'v1' => 'data2',
				'v2' => 'write',
				'v3' => null,
				'v4' => null,
				'v5' => null,
			],
			[
				'entity' => 'FastyBird\SimpleAuth\Tests\Fixtures\Entities\TestPolicyEntity',
				'type' => 'p',
				'v0' => 'administrator',
				'v1' => 'data1',
				'v2' => 'read',
				'v3' => null,
				'v4' => null,
				'v5' => null,
			],
			[
				'entity' => 'FastyBird\SimpleAuth\Tests\Fixtures\Entities\TestPolicyEntity',
				'type' => 'p',
				'v0' => 'administrator',
				'v1' => 'data1',
				'v2' => 'write',
				'v3' => null,
				'v4' => null,
				'v5' => null,
			],
			[
				'entity' => 'FastyBird\SimpleAuth\Entities\Policies\Policy',
				'type' => 'p',
				'v0' => 'testing',
				'v1' => 'data1',
				'v2' => 'read',
				'v3' => null,
				'v4' => null,
				'v5' => null,
			],
		], array_map(
			static function (Entities\Policies\Policy $policy): array {
				$data = array_merge(
					['entity' => $policy::class],
					$policy->toArray(),
				);
				unset($data['id']);

				return $data;
			},
			$policies,
		));

		$findPolices = new Queries\FindPolicies();

		$policies = $repository->findAllBy($findPolices);

		self::assertCount(13, $policies);

		self::assertFalse(
			$enforcer->enforce('78bee0f3-882a-47d9-88a5-2bbd78572690', 'data1', 'read'),
		);

		$result = $enforcer->addPermissionForUser(
			'78bee0f3-882a-47d9-88a5-2bbd78572690',
			'data1',
			'read',
		);
		self::assertTrue($result);

		$enforcer->invalidateCache();

		$findPolices = new Queries\FindPolicies();

		$policies = $repository->findAllBy($findPolices);

		self::assertCount(14, $policies);

		self::assertTrue(
			$enforcer->enforce('78bee0f3-882a-47d9-88a5-2bbd78572690', 'data1', 'read'),
		);

		$result = $enforcer->deletePermissionForUser(
			'78bee0f3-882a-47d9-88a5-2bbd78572690',
			'data1',
			'read',
		);
		self::assertTrue($result);

		$enforcer->invalidateCache();

		self::assertFalse(
			$enforcer->enforce('78bee0f3-882a-47d9-88a5-2bbd78572690', 'data1', 'read'),
		);
	}

}
