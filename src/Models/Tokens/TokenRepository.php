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
 * @template    TEntityClass of Entities\Tokens\Token
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Models
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class TokenRepository
{

	use Nette\SmartObject;

	/**
	 * @var array<ORM\EntityRepository>
	 *
	 * @phpstan-var array<ORM\EntityRepository<TEntityClass>>
	 */
	private array $repository = [];

	public function __construct(private Persistence\ManagerRegistry $managerRegistry)
	{
	}

	/**
	 * @param class-string $type
	 */
	public function findOneByIdentifier(
		string $identifier,
		string $type = Entities\Tokens\Token::class,
	): Entities\Tokens\Token|null
	{
		$findQuery = new Queries\FindTokens();
		$findQuery->byId(Uuid\Uuid::fromString($identifier));
		$findQuery->inState(Types\TokenState::STATE_ACTIVE);

		return $this->findOneBy($findQuery, $type);
	}

	/**
	 * @param Queries\FindTokens<Entities\Tokens\Token> $queryObject
	 * @param class-string $type
	 */
	public function findOneBy(
		Queries\FindTokens $queryObject,
		string $type = Entities\Tokens\Token::class,
	): Entities\Tokens\Token|null
	{
		return $queryObject->fetchOne($this->getRepository($type));
	}

	/**
	 * @param class-string $type
	 */
	public function findOneByToken(
		string $token,
		string $type = Entities\Tokens\Token::class,
	): Entities\Tokens\Token|null
	{
		$findQuery = new Queries\FindTokens();
		$findQuery->byToken($token);
		$findQuery->inState(Types\TokenState::STATE_ACTIVE);

		return $this->findOneBy($findQuery, $type);
	}

	/**
	 * @param class-string $type
	 *
	 * @return ORM\EntityRepository<TEntityClass>
	 */
	private function getRepository(string $type): ORM\EntityRepository
	{
		if (!isset($this->repository[$type])) {
			$repository = $this->managerRegistry->getRepository($type);

			// @phpstan-ignore-next-line
			if (!$repository instanceof ORM\EntityRepository) {
				throw new Exceptions\InvalidState('Entity repository could not be loaded');
			}

			// @phpstan-ignore-next-line
			$this->repository[$type] = $repository;
		}

		// @phpstan-ignore-next-line
		return $this->repository[$type];
	}

}
