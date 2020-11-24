<?php declare(strict_types = 1);

/**
 * ITokenRepository.php
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
 */
interface ITokenRepository
{

	/**
	 * @param string $identifier
	 * @param string|null $type
	 *
	 * @return Entities\Tokens\IToken|null
	 *
	 * @phpstan-template T of Entities\Tokens\Token
	 * @phpstan-param    string $identifier
	 * @phpstan-param    class-string<T> $type
	 */
	public function findOneByIdentifier(
		string $identifier,
		string $type = Entities\Tokens\Token::class
	): ?Entities\Tokens\IToken;

	/**
	 * @param string $token
	 * @param string|null $type
	 *
	 * @return Entities\Tokens\IToken|null
	 *
	 * @phpstan-template T of Entities\Tokens\Token
	 * @phpstan-param    string $token
	 * @phpstan-param    class-string<T> $type
	 */
	public function findOneByToken(
		string $token,
		string $type = Entities\Tokens\Token::class
	): ?Entities\Tokens\IToken;

	/**
	 * @param Queries\FindTokensQuery $queryObject
	 * @param string|null $type
	 *
	 * @return Entities\Tokens\IToken|null
	 *
	 * @phpstan-template T of Entities\Tokens\Token
	 * @phpstan-param    Queries\FindTokensQuery<T> $queryObject
	 * @phpstan-param    class-string<T> $type
	 */
	public function findOneBy(
		Queries\FindTokensQuery $queryObject,
		string $type = Entities\Tokens\Token::class
	): ?Entities\Tokens\IToken;

}
