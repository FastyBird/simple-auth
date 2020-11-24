<?php declare(strict_types = 1);

/**
 * IdentityFactory.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
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

	/**
	 * {@inheritDoc}
	 */
	public function create(JWT\Token $token): ?IIdentity
	{
		return new PlainIdentity(
			$token->getClaim(SimpleAuth\Constants::TOKEN_CLAIM_USER),
			$token->getClaim(SimpleAuth\Constants::TOKEN_CLAIM_ROLES)
		);
	}

}
