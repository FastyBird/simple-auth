<?php declare(strict_types = 1);

/**
 * FindTokensQuery.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Queries
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\SimpleAuth\Queries;

use Closure;
use Doctrine\ORM;
use FastyBird\SimpleAuth\Entities;
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Types;
use IPub\DoctrineOrmQuery;
use Ramsey\Uuid;

/**
 * Find tokens entities query
 *
 * @package          FastyBird:SimpleAuth!
 * @subpackage       Queries
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template T of Entities\Tokens\Token
 * @phpstan-extends  DoctrineOrmQuery\QueryObject<T>
 */
class FindTokensQuery extends DoctrineOrmQuery\QueryObject
{

	/** @var Closure[] */
	private array $filter = [];

	/** @var Closure[] */
	private array $select = [];

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return void
	 */
	public function byId(Uuid\UuidInterface $id): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($id): void {
			$qb->andWhere('t.id = :id')->setParameter('id', $id, Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @param string $token
	 *
	 * @return void
	 */
	public function byToken(string $token): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($token): void {
			$qb->andWhere('t.token = :token')->setParameter('token', $token);
		};
	}

	/**
	 * @param string $state
	 *
	 * @return void
	 *
	 * @throw Exceptions\InvalidArgumentException
	 */
	public function inState(string $state): void
	{
		if (!Types\TokenStateType::isValidValue($state)) {
			throw new Exceptions\InvalidArgumentException('Invalid token state given');
		}

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($state): void {
			$qb->andWhere('t.state = :state')->setParameter('state', $state);
		};
	}

	/**
	 * @param ORM\EntityRepository<Entities\Tokens\Token> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		return $this->createBasicDql($repository);
	}

	/**
	 * @param ORM\EntityRepository<Entities\Tokens\Token> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		return $this->createBasicDql($repository)->select('COUNT(t.id)');
	}

	/**
	 * @param ORM\EntityRepository<Entities\Tokens\Token> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	private function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('t');

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		foreach ($this->filter as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

}
