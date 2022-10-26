<?php declare(strict_types = 1);

/**
 * FindTokens.php
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
 * @template T of Entities\Tokens\Token
 * @extends  DoctrineOrmQuery\QueryObject<T>
 *
 * @package          FastyBird:SimpleAuth!
 * @subpackage       Queries
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 */
class FindTokens extends DoctrineOrmQuery\QueryObject
{

	/** @var Array<Closure> */
	private array $filter = [];

	/** @var Array<Closure> */
	private array $select = [];

	public function byId(Uuid\UuidInterface $id): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($id): void {
			$qb->andWhere('t.id = :id')->setParameter('id', $id, Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	public function byToken(string $token): void
	{
		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($token): void {
			$qb->andWhere('t.token = :token')->setParameter('token', $token);
		};
	}

	/**
	 * @throw Exceptions\InvalidArgument
	 */
	public function inState(string $state): void
	{
		if (!Types\TokenState::isValidValue($state)) {
			throw new Exceptions\InvalidArgument('Invalid token state given');
		}

		$this->filter[] = static function (ORM\QueryBuilder $qb) use ($state): void {
			$qb->andWhere('t.state = :state')->setParameter('state', $state);
		};
	}

	/**
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		return $this->createBasicDql($repository);
	}

	/**
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

	/**
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		return $this->createBasicDql($repository)->select('COUNT(t.id)');
	}

}
