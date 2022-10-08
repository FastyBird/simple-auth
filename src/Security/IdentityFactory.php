<?php declare(strict_types = 1);

/**
 * IdentityFactory.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           15.07.20
 */

namespace FastyBird\SimpleAuth\Security;

use FastyBird\SimpleAuth;
use Lcobucci\JWT;
use function strval;

/**
 * Application plain identity factory
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class IdentityFactory implements IIdentityFactory
{

	public function create(JWT\UnencryptedToken $token): IIdentity|null
	{
		$claims = $token->claims();

		return new PlainIdentity(
			strval($claims->get(SimpleAuth\Constants::TOKEN_CLAIM_USER)),
			// @phpstan-ignore-next-line
			$claims->get(SimpleAuth\Constants::TOKEN_CLAIM_ROLES),
		);
	}

}
