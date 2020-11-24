<?php declare(strict_types = 1);

/**
 * IAuthenticator.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           29.08.20
 */

namespace FastyBird\SimpleAuth\Security;

use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Security;

/**
 * Application authenticator interface
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IAuthenticator
{

	// Credential key
	public const USERNAME = 0;
	public const PASSWORD = 1;

	// Exception error code
	public const IDENTITY_NOT_FOUND = 1;
	public const INVALID_CREDENTIAL = 2;
	public const FAILURE = 3;
	public const NOT_APPROVED = 4;

	/**
	 * @param mixed[] $credentials
	 *
	 * @return Security\IIdentity
	 *
	 * @throws Exceptions\AuthenticationException
	 */
	public function authenticate(array $credentials): Security\IIdentity;

}
