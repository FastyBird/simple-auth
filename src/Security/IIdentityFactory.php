<?php declare(strict_types = 1);

/**
 * IIdentityFactory.php
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

use Lcobucci\JWT;

/**
 * Application identity factory interface
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IIdentityFactory
{

	/**
	 * @param JWT\Token $token
	 *
	 * @return IIdentity|null
	 */
	public function create(JWT\Token $token): ?IIdentity;

}
