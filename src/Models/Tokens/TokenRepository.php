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
use Nette;
use Ramsey\Uuid;

/**
 * Security token repository
 *
 * @template T of Entities\Tokens\Token
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Models
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class TokenRepository
{

	use Nette\SmartObject;

	/** @var Array<ORM\EntityRepository<T>> */
	private array $repository = [];

	public function __construct(private readonly Persistence\ManagerRegistry $managerRegistry)
	{
	}

	/**
	 * @phpstan-param class-string<T> $type
	 *
	 * @phpstan-return T|null
	 */
	public function findOneByIdentifier(
		string $identifier,
		string $type = Entities\Tokens\Token::class,
	): Entities\Tokens\Token|null
	{
		$findQuery = new Queries\FindTokens();
		$findQuery->byId(Uuid\Uuid::fromString($identifier));
		$findQuery->inState(Types\TokenState::STATE_ACTIVE);

		// @phpstan-ignore-next-line
		return $this->findOneBy($findQuery, $type);
	}

	/**
	 * @phpstan-param class-string<T> $type
	 *
	 * @phpstan-return T|null
	 */
	public function findOneByToken(
		string $token,
		string $type = Entities\Tokens\Token::class,
	): Entities\Tokens\Token|null
	{
		$findQuery = new Queries\FindTokens();
		$findQuery->byToken($token);
		$findQuery->inState(Types\TokenState::STATE_ACTIVE);

		// @phpstan-ignore-next-line
		return $this->findOneBy($findQuery, $type);
	}

	/**
	 * @phpstan-param Queries\FindTokens<T> $queryObject
	 * @phpstan-param class-string<T> $type
	 *
	 * @phpstan-return T|null
	 */
	public function findOneBy(
		Queries\FindTokens $queryObject,
		string $type = Entities\Tokens\Token::class,
	): Entities\Tokens\Token|null
	{
		return $queryObject->fetchOne($this->getRepository($type));
	}

	/**
	 * @param class-string<T> $type
	 *
	 * @return ORM\EntityRepository<T>
	 */
	private function getRepository(string $type): ORM\EntityRepository
	{
		if (!isset($this->repository[$type])) {
			$this->repository[$type] = $this->managerRegistry->getRepository($type);
		}

		return $this->repository[$type];
	}

}
