<?php declare(strict_types = 1);

/**
 * TokenState.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\SimpleAuth\Types;

use Consistence;
use function assert;
use function is_string;

/**
 * Doctrine2 DB type for token state column
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Types
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class TokenState extends Consistence\Enum\Enum
{

	/**
	 * Define states
	 */
	public const ACTIVE = 'active';

	public const BLOCKED = 'blocked';

	public const DELETED = 'deleted';

	public const EXPIRED = 'expired';

	public const REVOKED = 'revoked';

	public function getValue(): string
	{
		$value = parent::getValue();
		assert(is_string($value));

		return $value;
	}

	public function __toString(): string
	{
		return self::getValue();
	}

}
