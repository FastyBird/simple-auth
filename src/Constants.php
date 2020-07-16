<?php declare(strict_types = 1);

/**
 * Constants.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     common
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\NodeAuth;

/**
 * Library constants
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     common
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Constants
{

	/**
	 * Node ACL
	 */

	// Permissions string delimiter
	public const PERMISSIONS_DELIMITER = ':';

	/**
	 * Security tokens
	 */

	public const TOKEN_HEADER_NAME = 'authorization';
	public const TOKEN_HEADER_REGEXP = '/Bearer\s+(.*)$/i';

	public const TOKEN_TYPE_ACCESS = 'access';
	public const TOKEN_TYPE_REFRESH = 'refresh';

}
