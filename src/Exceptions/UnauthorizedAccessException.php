<?php declare(strict_types = 1);

/**
 * UnauthorizedAccessException.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Exceptions
 * @since          0.1.0
 *
 * @date           23.07.20
 */

namespace FastyBird\NodeAuth\Exceptions;

use RuntimeException;

class UnauthorizedAccessException extends RuntimeException implements IException
{

}
