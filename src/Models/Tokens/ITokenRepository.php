<?php declare(strict_types = 1);

/**
 * ITokenRepository.php
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

use FastyBird\SimpleAuth\Entities;
use FastyBird\SimpleAuth\Models;
use FastyBird\SimpleAuth\Queries;

/**
 * Security token repository interface
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template TEntityClass of Entities\Tokens\Token
 */
interface ITokenRepository
{

	/**
	 * @param string $identifier
	 * @param string $type
	 *
	 * @return Entities\Tokens\IToken|null
	 *
	 * @phpstan-param class-string<TEntityClass> $type
	 */
	public function findOneByIdentifier(
		string $identifier,
		string $type = Entities\Tokens\Token::class
	): ?Entities\Tokens\IToken;

	/**
	 * @param string $token
	 * @param string $type
	 *
	 * @return Entities\Tokens\IToken|null
	 *
	 * @phpstan-param class-string<TEntityClass> $type
	 */
	public function findOneByToken(
		string $token,
		string $type = Entities\Tokens\Token::class
	): ?Entities\Tokens\IToken;

	/**
	 * @param Queries\FindTokensQuery $queryObject
	 * @param string $type
	 *
	 * @return Entities\Tokens\IToken|null
	 *
	 * @phpstan-param Queries\FindTokensQuery<TEntityClass> $queryObject
	 * @phpstan-param class-string<TEntityClass> $type
	 */
	public function findOneBy(
		Queries\FindTokensQuery $queryObject,
		string $type = Entities\Tokens\Token::class
	): ?Entities\Tokens\IToken;

}
