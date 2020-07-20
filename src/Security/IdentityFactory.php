<?php declare(strict_types = 1);

/**
 * IdentityFactory.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           15.07.20
 */

namespace FastyBird\NodeAuth\Security;

use FastyBird\NodeAuth;
use Lcobucci\JWT;
use Nette\Security as NS;

/**
 * Application plain identity factory
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class IdentityFactory implements IIdentityFactory
{

	/**
	 * {@inheritDoc}
	 */
	public function create(JWT\Token $token): ?NS\IIdentity
	{
		return new PlainIdentity(
			$token->getClaim(NodeAuth\Constants::TOKEN_CLAIM_USER),
			$token->getClaim(NodeAuth\Constants::TOKEN_CLAIM_ROLES)
		);
	}

}
