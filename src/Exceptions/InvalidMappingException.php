<?php declare(strict_types = 1);

/**
 * InvalidMappingException.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Exceptions
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\NodeAuth\Exceptions;

use FastyBird\NodeLibs\Exceptions as NodeLibsExceptions;

class InvalidMappingException extends NodeLibsExceptions\InvalidArgumentException
{

}
