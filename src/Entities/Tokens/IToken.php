<?php declare(strict_types = 1);

/**
 * IToken.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           15.07.20
 */

namespace FastyBird\SimpleAuth\Entities\Tokens;

use FastyBird\SimpleAuth\Types;
use IPub\DoctrineCrud;

/**
 * Security token entity interface
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IToken extends DoctrineCrud\Entities\IEntity
{

	/**
	 * @param IToken $token
	 *
	 * @return void
	 */
	public function setParent(IToken $token): void;

	/**
	 * @return IToken|null
	 */
	public function getParent(): ?IToken;

	/**
	 * @return void
	 */
	public function removeParent(): void;

	/**
	 * @param IToken[] $children
	 *
	 * @return void
	 */
	public function setChildren(array $children): void;

	/**
	 * @param IToken $child
	 *
	 * @return void
	 */
	public function addChild(IToken $child): void;

	/**
	 * @return IToken[]
	 */
	public function getChildren(): array;

	/**
	 * @param IToken $child
	 *
	 * @return void
	 */
	public function removeChild(IToken $child): void;

	/**
	 * @return string
	 */
	public function getToken(): string;

	/**
	 * @param Types\TokenStateType $state
	 *
	 * @return void
	 */
	public function setState(Types\TokenStateType $state): void;

	/**
	 * @return Types\TokenStateType
	 */
	public function getState(): Types\TokenStateType;

	/**
	 * @return bool
	 */
	public function isActive(): bool;

	/**
	 * @return bool
	 */
	public function isBlocked(): bool;

	/**
	 * @return bool
	 */
	public function isDeleted(): bool;

}
