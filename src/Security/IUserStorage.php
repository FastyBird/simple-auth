<?php declare(strict_types = 1);

/**
 * IUserStorage.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           29.08.20
 */

namespace FastyBird\NodeAuth\Security;

use FastyBird\NodeAuth\Security;

/**
 * Application user storage
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IUserStorage
{

	/**
	 * @return bool
	 */
	public function isAuthenticated(): bool;

	/**
	 * @param IIdentity|null $identity
	 *
	 * @return void
	 */
	public function setIdentity(?Security\IIdentity $identity): void;

	/**
	 * @return Security\IIdentity|null
	 */
	public function getIdentity(): ?Security\IIdentity;

}
