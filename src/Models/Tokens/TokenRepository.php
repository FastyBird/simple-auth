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
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Queries;
use FastyBird\SimpleAuth\Types;
use Nette;
use Ramsey\Uuid;

/**
 * Security token repository
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template    TEntityClass of Entities\Tokens\Token
 * @phpstan-implements  ITokenRepository<TEntityClass>
 */
final class TokenRepository implements ITokenRepository
{

	use Nette\SmartObject;

	/**
	 * @var ORM\EntityRepository[]
	 *
	 * @phpstan-var ORM\EntityRepository<TEntityClass>[]
	 */
	private array $repository = [];

	/** @var Persistence\ManagerRegistry */
	private Persistence\ManagerRegistry $managerRegistry;

	public function __construct(Persistence\ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOneByIdentifier(
		string $identifier,
		string $type = Entities\Tokens\Token::class
	): ?Entities\Tokens\IToken {
		$findQuery = new Queries\FindTokensQuery();
		$findQuery->byId(Uuid\Uuid::fromString($identifier));
		$findQuery->inState(Types\TokenStateType::STATE_ACTIVE);

		return $this->findOneBy($findQuery, $type);
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOneByToken(
		string $token,
		string $type = Entities\Tokens\Token::class
	): ?Entities\Tokens\IToken {
		$findQuery = new Queries\FindTokensQuery();
		$findQuery->byToken($token);
		$findQuery->inState(Types\TokenStateType::STATE_ACTIVE);

		return $this->findOneBy($findQuery, $type);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @phpstan-param Queries\FindTokensQuery<Entities\Tokens\Token> $queryObject
	 */
	public function findOneBy(
		Queries\FindTokensQuery $queryObject,
		string $type = Entities\Tokens\Token::class
	): ?Entities\Tokens\IToken {
		/** @var Entities\Tokens\IToken|null $token */
		$token = $queryObject->fetchOne($this->getRepository($type));

		return $token;
	}

	/**
	 * @param string $type
	 *
	 * @return ORM\EntityRepository
	 *
	 * @phpstan-param class-string $type
	 *
	 * @phpstan-return ORM\EntityRepository<TEntityClass>
	 */
	private function getRepository(string $type): ORM\EntityRepository
	{
		if (!isset($this->repository[$type])) {
			$repository = $this->managerRegistry->getRepository($type);

			if (!$repository instanceof ORM\EntityRepository) {
				throw new Exceptions\InvalidStateException('Entity repository could not be loaded');
			}

			$this->repository[$type] = $repository;
		}

		return $this->repository[$type];
	}

}
