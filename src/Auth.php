<?php declare(strict_types = 1);

/**
 * Auth.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     common
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\NodeAuth;

use Nette;
use Nette\Security as NS;
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

	/** @var Security\TokenReader */
	private $tokenReader;

	/** @var Security\IIdentityFactory */
	private $identityFactory;

	/** @var NS\User */
	private $user;

	public function __construct(
		Security\TokenReader $tokenReader,
		Security\IIdentityFactory $identityFactory,
		NS\User $user
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
	 * @throws NS\AuthenticationException
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

		$this->user->logout(true);
	}

}
