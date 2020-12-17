<?php declare(strict_types = 1);

/**
 * Owner.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Mapping
 * @since          0.1.0
 *
 * @date           15.07.20
 */

namespace FastyBird\SimpleAuth\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Entity owner annotation for Doctrine2
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Mapping
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Owner extends Annotation
{

	/** @var string */
	public string $on = 'create';

	/** @var string|string[] */
	public $field;

	/** @var mixed */
	public $value;

	/** @var mixed[]|null */
	public $association;

}
