<?php declare(strict_types = 1);

/**
 * IToken.php
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

namespace FastyBird\NodeAuth\Entities\Tokens;

use FastyBird\NodeAuth\Types;
use IPub\DoctrineCrud;

/**
 * Security token entity interface
 *
 * @package        FastyBird:AuthNode!
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
	 * @param Types\TokenStatusType $status
	 *
	 * @return void
	 */
	public function setStatus(Types\TokenStatusType $status): void;

	/**
	 * @return Types\TokenStatusType
	 */
	public function getStatus(): Types\TokenStatusType;

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
