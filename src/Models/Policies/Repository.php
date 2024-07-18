<?php declare(strict_types = 1);

/**
 * Repository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           15.07.24
 */

namespace FastyBird\SimpleAuth\Models\Policies;

use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\SimpleAuth\Entities;
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Queries;
use IPub\DoctrineOrmQuery;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use Nette;
use Throwable;
use function is_array;

/**
 * Security token repository
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Models
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Repository
{

	use Nette\SmartObject;

	/** @var array<ORM\EntityRepository<Entities\Policies\Policy>> */
	private array $repository = [];

	public function __construct(private readonly Persistence\ManagerRegistry $managerRegistry)
	{
	}

	/**
	 * @template T of Entities\Policies\Policy
	 *
	 * @param Queries\FindPolicies<T> $queryObject
	 * @param class-string<T> $type
	 *
	 * @return T|null
	 *
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 */
	public function findOneBy(
		Queries\FindPolicies $queryObject,
		string $type = Entities\Policies\Policy::class,
	): Entities\Policies\Policy|null
	{
		return $queryObject->fetchOne($this->getRepository($type));
	}

	/**
	 * @template T of Entities\Policies\Policy
	 *
	 * @param Queries\FindPolicies<T> $queryObject
	 * @param class-string<T> $type
	 *
	 * @return array<T>
	 *
	 * @throws Exceptions\InvalidState
	 */
	public function findAllBy(
		Queries\FindPolicies $queryObject,
		string $type = Entities\Policies\Policy::class,
	): array
	{
		try {
			/** @var array<T> $result */
			$result = $this->getResultSet($queryObject, $type)->toArray();

			return $result;
		} catch (Throwable $ex) {
			throw new Exceptions\InvalidState('Fetch all data by query failed', $ex->getCode(), $ex);
		}
	}

	/**
	 * @template T of Entities\Policies\Policy
	 *
	 * @param Queries\FindPolicies<T> $queryObject
	 * @param class-string<T> $type
	 *
	 * @return DoctrineOrmQuery\ResultSet<T>
	 *
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 * @throws Exceptions\InvalidState
	 */
	public function getResultSet(
		Queries\FindPolicies $queryObject,
		string $type = Entities\Policies\Policy::class,
	): DoctrineOrmQuery\ResultSet
	{
		$result = $queryObject->fetch($this->getRepository($type));

		if (is_array($result)) {
			throw new Exceptions\InvalidState('Result set could not be created');
		}

		return $result;
	}

	/**
	 * @template T of Entities\Policies\Policy
	 *
	 * @param class-string<T> $type
	 *
	 * @return ORM\EntityRepository<T>
	 */
	private function getRepository(string $type): ORM\EntityRepository
	{
		if (!isset($this->repository[$type])) {
			$this->repository[$type] = $this->managerRegistry->getRepository($type);
		}

		/** @var ORM\EntityRepository<T> $repository */
		$repository = $this->repository[$type];

		return $repository;
	}

}
