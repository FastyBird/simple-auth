<?php declare(strict_types = 1);

/**
 * Auth.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     common
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\SimpleAuth;

use FastyBird\SimpleAuth\Exceptions as SimpleAuthExceptions;
use FastyBird\SimpleAuth\Security as SimpleAuthSecurity;
use Nette;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Authentication service
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     common
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Auth
{

	use Nette\SmartObject;

	/** @var SimpleAuthSecurity\TokenReader */
	private Security\TokenReader $tokenReader;

	/** @var SimpleAuthSecurity\IIdentityFactory */
	private Security\IIdentityFactory $identityFactory;

	/** @var SimpleAuthSecurity\User */
	private Security\User $user;

	public function __construct(
		SimpleAuthSecurity\TokenReader $tokenReader,
		SimpleAuthSecurity\IIdentityFactory $identityFactory,
		SimpleAuthSecurity\User $user
	) {
		$this->tokenReader = $tokenReader;
		$this->identityFactory = $identityFactory;

		$this->user = $user;
	}

	/**
	 * @param ServerRequestInterface $request
	 *
	 * @return void
	 *
	 * @throws SimpleAuthExceptions\AuthenticationException
	 */
	public function login(ServerRequestInterface $request): void
	{
		$token = $this->tokenReader->read($request);

		if ($token !== null) {
			$identity = $this->identityFactory->create($token);

			if ($identity !== null) {
				$this->user->login($identity);

				return;
			}
		}

		$this->user->logout();
	}

}
