<?php declare(strict_types = 1);

/**
 * TokenRepository.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\SimpleAuth\Models\Tokens;

use Doctrine\Common;
use Doctrine\Persistence;
use FastyBird\SimpleAuth\Entities;
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
 */
final class TokenRepository implements ITokenRepository
{

	use Nette\SmartObject;

	/** @var Common\Persistence\ManagerRegistry */
	private Common\Persistence\ManagerRegistry $managerRegistry;

	/** @var Persistence\ObjectRepository<Entities\Tokens\Token>[] */
	private array $repository = [];

	public function __construct(Common\Persistence\ManagerRegistry $managerRegistry)
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
	 * @return Persistence\ObjectRepository<Entities\Tokens\Token>
	 *
	 * @phpstan-template T of Entities\Tokens\Token
	 * @phpstan-param    class-string<T> $type
	 */
	private function getRepository(string $type): Persistence\ObjectRepository
	{
		if (!isset($this->repository[$type])) {
			$this->repository[$type] = $this->managerRegistry->getRepository($type);
		}

		return $this->repository[$type];
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

}
