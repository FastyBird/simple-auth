<?php declare(strict_types = 1);

/**
 * IEntityOwner.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           15.07.20
 */

namespace FastyBird\SimpleAuth\Entities;

/**
 * Entity owner interface
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IEntityOwner
{

	/**
	 * @param string|null $ownerId
	 */
	public function setOwnerId(?string $ownerId): void;

	/**
	 * @return string|null
	 */
	public function getOwnerId(): ?string;

}
