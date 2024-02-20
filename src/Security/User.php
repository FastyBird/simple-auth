<?php declare(strict_types = 1);

/**
 * User.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           29.08.20
 */

namespace FastyBird\SimpleAuth\Security;

use Closure;
use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Security;
use Nette;
use Nette\Utils;
use Ramsey\Uuid;
use function func_get_args;
use function in_array;

/**
 * Application user
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class User
{

	use Nette\SmartObject;

	/** @var array<Closure(Security\User $user): void> */
	public array $onLoggedIn = [];

	/** @var array<Closure(Security\User $user): void> */
	public array $onLoggedOut = [];

	private IUserStorage $storage;

	private IAuthenticator|null $authenticator;

	public function __construct(
		Security\IUserStorage $storage,
		Security\IAuthenticator|null $authenticator = null,
	)
	{
		$this->storage = $storage;
		$this->authenticator = $authenticator;
	}

	public function getId(): Uuid\UuidInterface|null
	{
		$identity = $this->getIdentity();

		return $identity?->getId();
	}

	public function getIdentity(): Security\IIdentity|null
	{
		return $this->storage->getIdentity();
	}

	/**
	 * @param string|Security\IIdentity $user name or instance of Security\IIdentity
	 *
	 * @throws Exceptions\Authentication
	 * @throws Exceptions\InvalidState
	 */
	public function login(string|Security\IIdentity $user, string|null $password = null): void
	{
		$this->logout();

		if (!$user instanceof Security\IIdentity) {
			if ($this->authenticator === null) {
				throw new Exceptions\InvalidState('Authenticator is not defined');
			}

			$user = $this->authenticator->authenticate(func_get_args());
		}

		$this->storage->setIdentity($user);

		Utils\Arrays::invoke($this->onLoggedIn, $this);
	}

	public function logout(): void
	{
		if ($this->isLoggedIn()) {
			Utils\Arrays::invoke($this->onLoggedOut, $this);
		}

		$this->storage->setIdentity(null);
	}

	public function isLoggedIn(): bool
	{
		return $this->storage->isAuthenticated();
	}

	public function isInRole(string $role): bool
	{
		return in_array($role, $this->getRoles(), true);
	}

	/**
	 * @return array<string>
	 */
	public function getRoles(): array
	{
		if (!$this->isLoggedIn()) {
			return [SimpleAuth\Constants::ROLE_ANONYMOUS];
		}

		$identity = $this->getIdentity();

		return $identity !== null && $identity->getRoles() !== []
			? $identity->getRoles()
			: [SimpleAuth\Constants::ROLE_USER];
	}

}
