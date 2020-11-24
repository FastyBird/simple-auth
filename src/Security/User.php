<?php declare(strict_types = 1);

/**
 * User.php
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

use Closure;
use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Security;
use Nette;
use Ramsey\Uuid;

/**
 * Application user
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @method onLoggedIn(Security\User $user)
 * @method onLoggedOut(Security\User $user)
 */
class User
{

	use Nette\SmartObject;

	/** @var Closure[] */
	public $onLoggedIn = [];

	/** @var Closure[] */
	public $onLoggedOut = [];

	/** @var Security\IUserStorage */
	private $storage;

	/** @var Security\IAuthenticator|null */
	private $authenticator;

	public function __construct(
		Security\IUserStorage $storage,
		?Security\IAuthenticator $authenticator = null
	) {
		$this->storage = $storage;
		$this->authenticator = $authenticator;
	}

	/**
	 * @return Uuid\UuidInterface|null
	 */
	public function getId(): ?Uuid\UuidInterface
	{
		$identity = $this->getIdentity();

		return $identity !== null ? $identity->getId() : null;
	}

	/**
	 * @return Security\IIdentity|null
	 */
	public function getIdentity(): ?Security\IIdentity
	{
		return $this->storage->getIdentity();
	}

	/**
	 * @param string|Security\IIdentity $user name or instance of Security\IIdentity
	 * @param string|null $password
	 *
	 * @return void
	 *
	 * @throws Exceptions\AuthenticationException
	 */
	public function login($user, ?string $password = null): void
	{
		$this->logout();

		if (!$user instanceof Security\IIdentity) {
			if ($this->authenticator === null) {
				throw new Exceptions\InvalidStateException('Authenticator is not defined');
			}

			$user = $this->authenticator->authenticate(func_get_args());
		}

		$this->storage->setIdentity($user);

		$this->onLoggedIn($this);
	}

	/**
	 * @return void
	 */
	public function logout(): void
	{
		if ($this->isLoggedIn()) {
			$this->onLoggedOut($this);
		}

		$this->storage->setIdentity(null);
	}

	/**
	 * @return bool
	 */
	public function isLoggedIn(): bool
	{
		return $this->storage->isAuthenticated();
	}

	/**
	 * @return string[]
	 */
	public function getRoles(): array
	{
		if (!$this->isLoggedIn()) {
			return [SimpleAuth\Constants::ROLE_ANONYMOUS];
		}

		$identity = $this->getIdentity();

		return $identity !== null && $identity->getRoles() !== [] ? $identity->getRoles() : [SimpleAuth\Constants::ROLE_USER];
	}

	/**
	 * @param string $role
	 *
	 * @return bool
	 */
	public function isInRole(string $role): bool
	{
		return in_array($role, $this->getRoles(), true);
	}

}
