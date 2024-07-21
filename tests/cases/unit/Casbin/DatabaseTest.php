<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit\Casbin;

use Casbin;
use FastyBird\SimpleAuth\Entities;
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Models;
use FastyBird\SimpleAuth\Queries;
use FastyBird\SimpleAuth\Security;
use FastyBird\SimpleAuth\Tests\Cases\Unit\DbTestCase;
use FastyBird\SimpleAuth\Tests\Fixtures;
use FastyBird\SimpleAuth\Types;
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
	 * @throws DI\MissingServiceException
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

		self::assertSame(
			['visitor', 'administrator'],
			$enforcerFactory->getEnforcer()->getAllRoles(),
		);

		self::assertFalse(
			$enforcerFactory->getEnforcer()->enforce('2784d750-f085-4580-8525-4d622face83d', 'data2', 'read'),
		);
		self::assertTrue(
			$enforcerFactory->getEnforcer()->enforce('2784d750-f085-4580-8525-4d622face83d', 'data3', 'read'),
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
			$enforcerFactory->getEnforcer()->enforce('2784d750-f085-4580-8525-4d622face83d', 'data2', 'read'),
		);

		$enforcerFactory->getEnforcer()->deletePermissionForUser(
			'2784d750-f085-4580-8525-4d622face83d',
			'data2',
			'read',
		);

		$findPolicy = new Queries\FindPolicies();
		$findPolicy->byId($createdPolicy->getId());

		$foundPolicy = $repository->findOneBy($findPolicy);

		self::assertNull($foundPolicy);

		self::assertFalse(
			$enforcerFactory->getEnforcer()->enforce('2784d750-f085-4580-8525-4d622face83d', 'data2', 'read'),
		);

		$enforcerFactory->getEnforcer()->addRoleForUser('2784d750-f085-4580-8525-4d622face83d', 'new_role');

		self::assertEqualsCanonicalizing([
			'new_role',
			'visitor',
		], $enforcerFactory->getEnforcer()->getRolesForUser('2784d750-f085-4580-8525-4d622face83d'));

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
	}

}
