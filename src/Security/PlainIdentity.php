<?php declare(strict_types = 1);

/**
 * PlainIdentity.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\NodeAuth\Security;

use Nette;
use Nette\Security as NS;

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
