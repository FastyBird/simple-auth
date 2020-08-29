<?php declare(strict_types = 1);

/**
 * IIdentity.php
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

use Ramsey\Uuid;

/**
 * Application identity interface
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IIdentity
{

	/**
	 * @return Uuid\UuidInterface
	 */
	public function getId(): Uuid\UuidInterface;

	/**
	 * @return string[]
	 */
	public function getRoles(): array;

}
