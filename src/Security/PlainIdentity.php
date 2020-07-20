<?php declare(strict_types = 1);

/**
 * PlainIdentity.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           15.07.20
 */

namespace FastyBird\NodeAuth\Security;

use Nette;
use Nette\Security as NS;

/**
 * System basic plain identity
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class PlainIdentity implements NS\IIdentity
{

	use Nette\SmartObject;

	/** @var string */
	private $id;

	/** @var mixed[] */
	private $roles;

	/**
	 * @param string $id
	 * @param mixed[] $roles
	 */
	public function __construct(string $id, array $roles = [])
	{
		$this->id = $id;
		$this->roles = $roles;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return mixed[]
	 */
	public function getRoles(): array
	{
		return $this->roles;
	}

}
