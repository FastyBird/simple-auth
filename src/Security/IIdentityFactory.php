<?php declare(strict_types = 1);

/**
 * IIdentityFactory.php
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

use Lcobucci\JWT;

/**
 * Application identity factory interface
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IIdentityFactory
{

	/**
	 * @param JWT\UnencryptedToken $token
	 *
	 * @return IIdentity|null
	 */
	public function create(JWT\UnencryptedToken $token): ?IIdentity;

}
