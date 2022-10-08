<?php declare(strict_types = 1);

/**
 * Owner.php
 *
 * @license        More in LICENSE.md
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

	public string $on = 'create';

	/** @var string|array<string> */
	public string|array $field;

	/** @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint */
	public $value;

	/** @var array<mixed>|null */
	public array|null $association = null;

}
