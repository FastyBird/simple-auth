<?php declare(strict_types = 1);

/**
 * TokenRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\SimpleAuth\Models\Tokens;

use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\SimpleAuth\Entities;
use FastyBird\SimpleAuth\Queries;
use FastyBird\SimpleAuth\Types;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use Nette;
use Ramsey\Uuid;
use function assert;

/**
 * Security token repository
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Models
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class TokenRepository
{

	use Nette\SmartObject;

	/** @var array<ORM\EntityRepository<Entities\Tokens\Token>> */
	private array $repository = [];

	public function __construct(private readonly Persistence\ManagerRegistry $managerRegistry)
	{
	}

	/**
	 * @template T of Entities\Tokens\Token
	 *
	 * @param class-string<T> $type
	 *
	 * @return T|null
	 *
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 */
	public function findOneByIdentifier(
		string $identifier,
		string $type = Entities\Tokens\Token::class,
	): Entities\Tokens\Token|null
	{
		$findQuery = new Queries\FindTokens();
		$findQuery->byId(Uuid\Uuid::fromString($identifier));
		$findQuery->inState(Types\TokenState::ACTIVE);

		$result = $this->findOneBy($findQuery, $type);
		assert($result instanceof $type || $result === null);

		return $result;
	}

	/**
	 * @template T of Entities\Tokens\Token
	 *
	 * @param class-string<T> $type
	 *
	 * @return T|null
	 *
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 */
	public function findOneByToken(
		string $token,
		string $type = Entities\Tokens\Token::class,
	): Entities\Tokens\Token|null
	{
		$findQuery = new Queries\FindTokens();
		$findQuery->byToken($token);
		$findQuery->inState(Types\TokenState::ACTIVE);

		$result = $this->findOneBy($findQuery, $type);
		assert($result instanceof $type || $result === null);

		return $result;
	}

	/**
	 * @template T of Entities\Tokens\Token
	 *
	 * @param Queries\FindTokens<T> $queryObject
	 * @param class-string<T> $type
	 *
	 * @return T|null
	 *
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 */
	public function findOneBy(
		Queries\FindTokens $queryObject,
		string $type = Entities\Tokens\Token::class,
	): Entities\Tokens\Token|null
	{
		return $queryObject->fetchOne($this->getRepository($type));
	}

	/**
	 * @template T of Entities\Tokens\Token
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
