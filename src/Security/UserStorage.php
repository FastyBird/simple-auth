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

use FastyBird\SimpleAuth\Security;

/**
 * Application user storage
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class UserStorage implements Security\IUserStorage
{

	/** @var Security\IIdentity|null */
	private $identity = null;

	/**
	 * {@inheritDoc}
	 */
	public function isAuthenticated(): bool
	{
		return $this->getIdentity() !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setIdentity(?Security\IIdentity $identity = null): void
	{
		$this->identity = $identity;
	}

	/**
	 * @return Security\IIdentity|null
	 */
	public function getIdentity(): ?Security\IIdentity
	{
		return $this->identity;
	}

}
