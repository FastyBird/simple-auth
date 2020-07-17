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

	/** @var string|null */
	private $accessToken = null;

	/** @var Security\TokenValidator */
	private $tokenValidator;

	/** @var Security\IIdentityFactory */
	private $identityFactory;

	/** @var NS\User */
	private $user;

	public function __construct(
		Security\TokenValidator $tokenValidator,
		Security\IIdentityFactory $identityFactory,
		NS\User $user
	) {
		$this->tokenValidator = $tokenValidator;
		$this->identityFactory = $identityFactory;

		$this->user = $user;
	}

	/**
	 * @param string $token
	 *
	 * @return void
	 */
	public function setAccessToken(string $token): void
	{
		$this->accessToken = $token;
	}

	/**
	 * @return string|null
	 */
	public function getAccessToken(): ?string
	{
		return $this->accessToken;
	}

	/**
	 * @return void
	 *
	 * @throws NS\AuthenticationException
	 */
	public function login(): void
	{
		if ($this->accessToken !== null) {
			$token = $this->tokenValidator->validate($this->accessToken);

			if ($token !== null) {
				$identity = $this->identityFactory->create($token);

				if ($identity !== null) {
					$this->user->login($identity);
				}
			}
		}
	}

}
