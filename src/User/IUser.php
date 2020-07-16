<?php declare(strict_types = 1);

/**
 * IUser.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     User
 * @since          0.1.0
 *
 * @date           14.07.20
 */

namespace FastyBird\NodeAuth\User;

interface IUser
{

	/**
	 * @return string
	 */
	public function getId(): string;

	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return mixed[]
	 */
	public function getRoles(): array;

}
