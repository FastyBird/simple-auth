<?php declare(strict_types = 1);

/**
 * FindPolicies.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Queries
 * @since          0.1.0
 *
 * @date           15.07.24
 */

namespace FastyBird\SimpleAuth\Queries;

use Closure;
use Doctrine\ORM;
use FastyBird\SimpleAuth\Entities;
use IPub\DoctrineOrmQuery;
use Ramsey\Uuid;

/**
 * Find tokens entities query
 *
 * @template T of Entities\Policies\Policy
 * @extends  DoctrineOrmQuery\QueryObject<T>
 *
 * @package          FastyBird:SimpleAuth!
 * @subpackage       Queries
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 */
class FindPolicies extends DoctrineOrmQuery\QueryObject
{

	/** @var array<Closure(ORM\QueryBuilder $qb): void> */
	private array $filter = [];

	/** @var array<Closure(ORM\QueryBuilder $qb): void> */
	private array $select = [];

	public function byId(Uuid\UuidInterface $id): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($id): void {
			$qb->andWhere('p.id = :id')->setParameter('id', $id, Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	public function byType(string $type): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($type): void {
			$qb->andWhere('p.type = :type')->setParameter('type', $type);
		};
	}

	/**
	 * @param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		return $this->createBasicDql($repository);
	}

	/**
	 * @param ORM\EntityRepository<T> $repository
	 */
	private function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('p');

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		foreach ($this->filter as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

	/**
	 * @param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		return $this->createBasicDql($repository)->select('COUNT(p.id)');
	}

}
