<?php declare(strict_types = 1);

/**
 * TEntityOwner.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           15.07.20
 */

namespace FastyBird\NodeAuth\Entities;

use FastyBird\NodeAuth\Mapping\Annotation as FB;

/**
 * Entity owner entity
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
trait TEntityOwner
{

	/**
	 * @var mixed|NULL
	 *
	 * @FB\Owner(on="create")
	 */
	protected $owner;

	/**
	 * @param mixed $ownerId
	 *
	 * @return void
	 */
	public function setOwnerId($ownerId): void
	{
		$this->owner = $ownerId;
	}

	/**
	 * @return mixed|NULL
	 */
	public function getOwnerId()
	{
		return $this->owner;
	}

}
