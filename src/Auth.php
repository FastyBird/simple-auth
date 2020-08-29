<?php declare(strict_types = 1);

/**
 * Auth.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     common
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\NodeAuth;

use FastyBird\NodeAuth\Exceptions as NodeAuthExceptions;
use FastyBird\NodeAuth\Security as NodeAuthSecurity;
use Nette;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Authentication service
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     common
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Auth
{

	use Nette\SmartObject;

	/** @var NodeAuthSecurity\TokenReader */
	private $tokenReader;

	/** @var NodeAuthSecurity\IIdentityFactory */
	private $identityFactory;

	/** @var NodeAuthSecurity\User */
	private $user;

	public function __construct(
		NodeAuthSecurity\TokenReader $tokenReader,
		NodeAuthSecurity\IIdentityFactory $identityFactory,
		NodeAuthSecurity\User $user
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
	 * @throws NodeAuthExceptions\AuthenticationException
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
