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

namespace FastyBird\SimpleAuth\Mapping\Driver;

use Doctrine\Common;
use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Mapping;
use Nette;
use function array_reverse;
use function assert;
use function class_parents;
use function in_array;
use function sprintf;
use function str_replace;
use function strtoupper;

/**
 * Doctrine owner annotation driver
 *
 * @template T of object
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Mapping
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Owner
{

	use Nette\SmartObject;

	private const EXTENSION_ANNOTATION = 'FastyBird\SimpleAuth\Mapping\Annotation\Owner';

	/**
	 * List of cached object configurations
	 *
	 * @var array<mixed>
	 */
	private static array $objectConfigurations = [];

	/**
	 * List of types which are valid for blame
	 *
	 * @var array<string>
	 */
	private array $validTypes = [
		'string',
	];

	private Common\Annotations\Reader $annotationReader;

	private Common\Cache\Cache $cacheDriver;

	public function __construct(Common\Cache\Cache $cache)
	{
		$this->cacheDriver = $cache;
		$this->annotationReader = new Common\Annotations\PsrCachedReader(
			new Common\Annotations\AnnotationReader(),
			Common\Cache\Psr6\CacheAdapter::wrap($cache),
		);
	}

	/**
	 * Get the configuration for specific object class
	 * if cache driver is present it scans it also
	 *
	 * @return array<mixed>
	 *
	 * @throws Common\Annotations\AnnotationException
	 * @throws ORM\Mapping\MappingException
	 *
	 * @phpstan-param class-string $class
	 */
	public function getObjectConfigurations(Persistence\ObjectManager $objectManager, string $class): array
	{
		$config = [];

		if (isset(self::$objectConfigurations[$class])) {
			$config = self::$objectConfigurations[$class];

		} else {
			$metadataFactory = $objectManager->getMetadataFactory();
			assert($metadataFactory instanceof ORM\Mapping\ClassMetadataFactory);

			$cacheId = self::getCacheId($class);

			if (($cached = $this->cacheDriver->fetch($cacheId)) !== false) {
				self::$objectConfigurations[$class] = $cached;
				$config = $cached;

			} else {
				/** @phpstan-var ORM\Mapping\ClassMetadata<T> $classMetadata */
				$classMetadata = $metadataFactory->getMetadataFor($class);

				// Re-generate metadata on cache miss
				$this->loadMetadataForObjectClass($objectManager, $classMetadata);

				if (isset(self::$objectConfigurations[$class])) {
					$config = self::$objectConfigurations[$class];
				}
			}

			// @phpstan-ignore-next-line
			$objectClass = $config['useObjectClass'] ?? $class;

			if ($objectClass !== $class) {
				// @phpstan-ignore-next-line
				$this->getObjectConfigurations($objectManager, $objectClass);
			}
		}

		// @phpstan-ignore-next-line
		return $config;
	}

	/**
	 * Get the cache id
	 */
	private static function getCacheId(string $className): string
	{
		return $className . '\\$' . strtoupper(str_replace('\\', '_', __NAMESPACE__)) . '_CLASSMETADATA';
	}

	/**
	 * @throws ORM\Mapping\MappingException
	 *
	 * @phpstan-param ORM\Mapping\ClassMetadata<T> $classMetadata
	 */
	public function loadMetadataForObjectClass(
		Persistence\ObjectManager $objectManager,
		ORM\Mapping\ClassMetadata $classMetadata,
	): void
	{
		if ($classMetadata->isMappedSuperclass) {
			return; // Ignore mappedSuperclasses for now
		}

		// The annotation reader accepts a ReflectionClass, which can be
		// obtained from the $classMetadata
		$reflectionClass = $classMetadata->getReflectionClass();

		$config = [];

		$useObjectName = $classMetadata->getName();

		$metadataFactory = $objectManager->getMetadataFactory();
		assert($metadataFactory instanceof ORM\Mapping\ClassMetadataFactory);

		$classParents = class_parents($classMetadata->getName());

		if ($classParents === false) {
			return;
		}

		// Collect metadata from inherited classes
		foreach (array_reverse($classParents) as $parentClass) {
			// Read only inherited mapped classes
			if ($metadataFactory->hasMetadataFor($parentClass)) {
				/** @phpstan-var ORM\Mapping\ClassMetadata<T> $parentClassMetadata */
				$parentClassMetadata = $objectManager->getClassMetadata($parentClass);

				$config = $this->readExtendedMetadata($parentClassMetadata, $config);

				$isBaseInheritanceLevel = !$parentClassMetadata->isInheritanceTypeNone()
					&& $parentClassMetadata->parentClasses !== []
					&& $config !== [];

				if ($isBaseInheritanceLevel === true) {
					$useObjectName = $reflectionClass->getName();
				}
			}
		}

		$config = $this->readExtendedMetadata($classMetadata, $config);

		if ($config !== []) {
			$config['useObjectClass'] = $useObjectName;
		}

		// Cache the metadata (even if it's empty)
		// Caching empty metadata will prevent re-parsing non-existent annotations
		$cacheId = self::getCacheId($classMetadata->getName());

		$this->cacheDriver->save($cacheId, $config);

		self::$objectConfigurations[$classMetadata->getName()] = $config;
	}

	/**
	 * @param array<mixed> $config
	 *
	 * @return array<mixed>
	 *
	 * @throws ORM\Mapping\MappingException
	 *
	 * @phpstan-param ORM\Mapping\ClassMetadata<T> $classMetadata
	 */
	private function readExtendedMetadata(ORM\Mapping\ClassMetadata $classMetadata, array $config): array
	{
		$class = $classMetadata->getReflectionClass();

		// Property annotations
		foreach ($class->getProperties() as $property) {
			if ($classMetadata->isMappedSuperclass && $property->isPrivate() === false ||
				$classMetadata->isInheritedField($property->getName()) ||
				isset($classMetadata->associationMappings[$property->getName()]['inherited'])
			) {
				continue;
			}

			$owner = $this->annotationReader->getPropertyAnnotation($property, self::EXTENSION_ANNOTATION);

			if ($owner instanceof Mapping\Annotation\Owner) {
				$field = $property->getName();

				// No map field nor association
				if (
					$classMetadata->hasField($field) === false
					&& $classMetadata->hasAssociation($field) === false
				) {
					$classMetadata->mapField([
						'fieldName' => $field,
						'type' => 'string',
						'nullable' => true,
					]);
				}

				if ($classMetadata->hasField($field) && $this->isValidField($classMetadata, $field) === false) {
					throw new Exceptions\InvalidMapping(
						sprintf(
							'Field - [%s] type is not valid and must be "string" in class - %s',
							$field,
							$classMetadata->getName(),
						),
					);
				} elseif (
					$classMetadata->hasAssociation($field)
					&& $classMetadata->isSingleValuedAssociation(
						$field,
					) === false
				) {
					throw new Exceptions\InvalidMapping(
						sprintf(
							'Association - [%s] is not valid, it must be a string field - %s',
							$field,
							$classMetadata->getName(),
						),
					);
				}

				// Check for valid events
				if (!in_array($owner->on, ['create'], true)) {
					throw new Exceptions\InvalidMapping(
						sprintf(
							'Field - [%s] trigger "on" is not one of [create] in class - %s',
							$field,
							$classMetadata->getName(),
						),
					);
				}

				// @phpstan-ignore-next-line
				$config[$owner->on][] = $field;
			}
		}

		return $config;
	}

	/**
	 * Checks if $field type is valid
	 *
	 * @throws ORM\Mapping\MappingException
	 *
	 * @phpstan-param ORM\Mapping\ClassMetadata<T> $classMetadata
	 */
	private function isValidField(ORM\Mapping\ClassMetadata $classMetadata, string $field): bool
	{
		$mapping = $classMetadata->getFieldMapping($field);

		return in_array($mapping['type'], $this->validTypes, true);
	}

}
