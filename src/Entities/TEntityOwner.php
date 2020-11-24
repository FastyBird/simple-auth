<?php declare(strict_types = 1);

/**
 * TEntityOwner.php
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

use FastyBird\SimpleAuth\Mapping\Annotation as FB;

/**
 * Entity owner entity
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
trait TEntityOwner
{

	/**
	 * @var mixed|null
	 *
	 * @FB\Owner(on="create")
	 */
	protected $owner;

	/**
	 * @param string|null $ownerId
	 *
	 * @return void
	 */
	public function setOwnerId(?string $ownerId): void
	{
		$this->owner = $ownerId;
	}

	/**
	 * @return string|null
	 */
	public function getOwnerId(): ?string
	{
		return $this->owner;
	}

}
