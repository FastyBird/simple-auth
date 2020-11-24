<?php declare(strict_types = 1);

/**
 * ForbiddenAccessException.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Exceptions
 * @since          0.1.0
 *
 * @date           15.07.20
 */

namespace FastyBird\SimpleAuth\Exceptions;

use RuntimeException;

class ForbiddenAccessException extends RuntimeException implements IException
{

}
