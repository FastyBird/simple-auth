<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit\Models;

use Doctrine\ORM;
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Models;
use FastyBird\SimpleAuth\Queries;
use FastyBird\SimpleAuth\Tests\Cases\Unit\BaseTestCase;
use FastyBird\SimpleAuth\Tests\Fixtures;
use FastyBird\SimpleAuth\Types;
use IPub\DoctrineCrud\Exceptions as DoctrineCrudExceptions;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use Nette\DI;
use Nette\Utils;
use Ramsey\Uuid;

final class PoliciesRepositoryTest extends BaseTestCase
{

	/**
	 * @throws DI\MissingServiceException
	 * @throws DoctrineCrudExceptions\InvalidArgumentException
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 * @throws DoctrineCrudExceptions\InvalidArgumentException
	 * @throws ORM\Tools\ToolsException
	 */
	public function testPolicyEntity(): void
	{
		$this->generateDbSchema();

		$manager = $this->container->getByType(Models\Policies\Manager::class);

		$repository = $this->container->getByType(Models\Policies\Repository::class);

		$parentId = Uuid\Uuid::fromString('ff11f4fd-c06b-40a2-9a79-6dd3e3a10373');

		$policyEntity = $manager->create(Utils\ArrayHash::from([
			'entity' => Fixtures\Entities\TestPolicyEntity::class,
			'parent' => $parentId,
			'type' => Types\PolicyType::POLICY,
			'v0' => '2784d750-f085-4580-8525-4d622face83d',
			'v1' => 'data1',
			'v2' => 'read',
		]));

		self::assertInstanceOf(Fixtures\Entities\TestPolicyEntity::class, $policyEntity);
		self::assertSame($parentId->toString(), $policyEntity->getParent()?->toString());
		self::assertSame('read', $policyEntity->getV2());

		$findPolicy = new Queries\FindPolicies();
		$findPolicy->byId($policyEntity->getId());

		$foundPolicy = $repository->findOneBy($findPolicy);

		self::assertInstanceOf(Fixtures\Entities\TestPolicyEntity::class, $foundPolicy);
		self::assertSame($parentId->toString(), $foundPolicy->getParent()?->toString());

		$updatedPolicyEntity = $manager->update($policyEntity, Utils\ArrayHash::from([
			'type' => 'p',
			'v0' => '2784d750-f085-4580-8525-4d622face83d',
			'v1' => 'data1',
			'v2' => 'write',
		]));

		self::assertInstanceOf(Fixtures\Entities\TestPolicyEntity::class, $updatedPolicyEntity);
		self::assertSame('write', $policyEntity->getV2());

		$result = $manager->delete($policyEntity);

		self::assertTrue($result);

		$findPolicy = new Queries\FindPolicies();
		$findPolicy->byId($policyEntity->getId());

		$foundPolicy = $repository->findOneBy($findPolicy);

		self::assertNull($foundPolicy);
	}

	/**
	 * @throws DI\MissingServiceException
	 * @throws Exceptions\InvalidState
	 * @throws ORM\Tools\ToolsException
	 */
	public function testFetchEntities(): void
	{
		$this->generateDbSchema();

		$manager = $this->container->getByType(Models\Policies\Manager::class);

		$repository = $this->container->getByType(Models\Policies\Repository::class);

		$parentId = Uuid\Uuid::fromString('ff11f4fd-c06b-40a2-9a79-6dd3e3a10373');

		$manager->create(Utils\ArrayHash::from([
			'entity' => Fixtures\Entities\TestPolicyEntity::class,
			'parent' => $parentId,
			'type' => Types\PolicyType::POLICY,
			'v0' => '2784d750-f085-4580-8525-4d622face83d',
			'v1' => 'data1',
			'v2' => 'read',
		]));

		$manager->create(Utils\ArrayHash::from([
			'entity' => Fixtures\Entities\TestPolicyEntity::class,
			'parent' => $parentId,
			'type' => Types\PolicyType::POLICY,
			'v0' => '2784d750-f085-4580-8525-4d622face83d',
			'v1' => 'data1',
			'v2' => 'write',
		]));

		$manager->create(Utils\ArrayHash::from([
			'entity' => Fixtures\Entities\TestPolicyEntity::class,
			'parent' => $parentId,
			'type' => Types\PolicyType::POLICY,
			'v0' => '2784d750-f085-4580-8525-4d622face83d',
			'v1' => 'data1',
			'v2' => 'remove',
		]));

		$findQuery = new Queries\FindPolicies();

		$policies = $repository->findAllBy($findQuery);

		self::assertCount(3, $policies);

		$findQuery = new Queries\FindPolicies();
		$findQuery->byType(Types\PolicyType::POLICY);

		$policies = $repository->findAllBy($findQuery);

		self::assertCount(3, $policies);

		$findQuery = new Queries\FindPolicies();
		$findQuery->byType(Types\PolicyType::ROLE);

		$policies = $repository->findAllBy($findQuery);

		self::assertCount(0, $policies);
	}

}
