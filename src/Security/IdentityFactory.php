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
		$claims = $token->claims();

		return new PlainIdentity(
			$claims->get(SimpleAuth\Constants::TOKEN_CLAIM_USER),
			$claims->get(SimpleAuth\Constants::TOKEN_CLAIM_ROLES)
		);
	}

}
