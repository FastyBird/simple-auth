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

namespace FastyBird\SimpleAuth\Mapping\Driver;

use Doctrine\Common;
use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Mapping;
use Nette;

/**
 * Doctrine owner annotation driver
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Mapping
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Owner
{

	use Nette\SmartObject;

	private const EXTENSION_ANNOTATION = 'FastyBird\SimpleAuth\Mapping\Annotation\Owner';

	/**
	 * List of cached object configurations
	 *
	 * @var mixed[]
	 */
	private static $objectConfigurations = [];

	/**
	 * List of types which are valid for blame
	 *
	 * @var string[]
	 */
	private $validTypes = [
		'string',
	];

	/**
	 * @param Persistence\ObjectManager $objectManager
	 * @param ORM\Mapping\ClassMetadata $classMetadata
	 *
	 * @return void
	 *
	 * @throws ORM\Mapping\MappingException
	 */
	public function loadMetadataForObjectClass(
		Persistence\ObjectManager $objectManager,
		ORM\Mapping\ClassMetadata $classMetadata
	): void {
		if ($classMetadata->isMappedSuperclass) {
			return; // Ignore mappedSuperclasses for now
		}

		// The annotation reader accepts a ReflectionClass, which can be
		// obtained from the $classMetadata
		$reflectionClass = $classMetadata->getReflectionClass();

		$config = [];

		$useObjectName = $classMetadata->getName();

		/** @var ORM\Mapping\ClassMetadataFactory $metadataFactory */
		$metadataFactory = $objectManager->getMetadataFactory();

		// Collect metadata from inherited classes
		foreach (array_reverse(class_parents($classMetadata->getName())) as $parentClass) {
			// Read only inherited mapped classes
			if ($metadataFactory->hasMetadataFor($parentClass)) {
				/** @var ORM\Mapping\ClassMetadata $parentClassMetadata */
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

		$cacheDriver = $metadataFactory->getCacheDriver();

		if ($cacheDriver !== null) {
			$cacheDriver->save($cacheId, $config);
		}

		self::$objectConfigurations[$classMetadata->getName()] = $config;
	}

	/**
	 * @param ORM\Mapping\ClassMetadata $metadata
	 * @param mixed[] $config
	 *
	 * @return mixed[]
	 *
	 * @throws ORM\Mapping\MappingException
	 */
	private function readExtendedMetadata(ORM\Mapping\ClassMetadata $metadata, array $config): array
	{
		$class = $metadata->getReflectionClass();

		// Create doctrine annotation reader
		$reader = $this->getDefaultAnnotationReader();

		// Property annotations
		foreach ($class->getProperties() as $property) {
			if ($metadata->isMappedSuperclass && $property->isPrivate() === false ||
				$metadata->isInheritedField($property->getName()) ||
				isset($metadata->associationMappings[$property->getName()]['inherited'])
			) {
				continue;
			}

			$owner = $reader->getPropertyAnnotation($property, self::EXTENSION_ANNOTATION);

			if ($owner instanceof Mapping\Annotation\Owner) {
				$field = $property->getName();

				// No map field nor association
				if (
					$metadata->hasField($field) === false
					&& $metadata->hasAssociation($field) === false
				) {
					$metadata->mapField([
						'fieldName' => $field,
						'type'      => 'string',
						'nullable'  => true,
					]);
				}

				if ($metadata->hasField($field) && $this->isValidField($metadata, $field) === false) {
					throw new Exceptions\InvalidMappingException(
						sprintf('Field - [%s] type is not valid and must be "string" in class - %s', $field, $metadata->getName())
					);

				} elseif ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field) === false) {
					throw new Exceptions\InvalidMappingException(
						sprintf('Association - [%s] is not valid, it must be a string field - %s', $field, $metadata->getName())
					);
				}

				// Check for valid events
				if (!in_array($owner->on, ['create'], true)) {
					throw new Exceptions\InvalidMappingException(
						sprintf('Field - [%s] trigger "on" is not one of [create] in class - %s', $field, $metadata->getName())
					);
				}

				$config[$owner->on][] = $field;
			}
		}

		return $config;
	}

	/**
	 * Get the configuration for specific object class
	 * if cache driver is present it scans it also
	 *
	 * @param Persistence\ObjectManager $objectManager
	 * @param string $class
	 *
	 * @return mixed[]
	 *
	 * @throws Common\Annotations\AnnotationException
	 * @throws ORM\Mapping\MappingException
	 */
	public function getObjectConfigurations(Persistence\ObjectManager $objectManager, string $class): array
	{
		$config = [];

		if (isset(self::$objectConfigurations[$class])) {
			$config = self::$objectConfigurations[$class];

		} else {
			/** @var ORM\Mapping\ClassMetadataFactory $metadataFactory */
			$metadataFactory = $objectManager->getMetadataFactory();

			/** @var Common\Cache\Cache|null $cacheDriver */
			$cacheDriver = $metadataFactory->getCacheDriver();

			if ($cacheDriver !== null) {
				$cacheId = self::getCacheId($class);

				if (($cached = $cacheDriver->fetch($cacheId)) !== false) {
					self::$objectConfigurations[$class] = $cached;
					$config = $cached;

				} else {
					/** @var ORM\Mapping\ClassMetadata $classMetadata */
					$classMetadata = $metadataFactory->getMetadataFor($class);

					// Re-generate metadata on cache miss
					$this->loadMetadataForObjectClass($objectManager, $classMetadata);

					if (isset(self::$objectConfigurations[$class])) {
						$config = self::$objectConfigurations[$class];
					}
				}

				$objectClass = $config['useObjectClass'] ?? $class;

				if ($objectClass !== $class) {
					$this->getObjectConfigurations($objectManager, $objectClass);
				}
			}
		}

		return $config;
	}

	/**
	 * Create default annotation reader for extensions
	 *
	 * @return Common\Annotations\CachedReader
	 */
	private function getDefaultAnnotationReader(): Common\Annotations\CachedReader
	{
		$reader = new Common\Annotations\AnnotationReader();

		return new Common\Annotations\CachedReader($reader, new Common\Cache\ArrayCache());
	}

	/**
	 * Checks if $field type is valid
	 *
	 * @param ORM\Mapping\ClassMetadata $meta
	 * @param string $field
	 *
	 * @return bool
	 *
	 * @throws ORM\Mapping\MappingException
	 */
	private function isValidField(ORM\Mapping\ClassMetadata $meta, string $field): bool
	{
		$mapping = $meta->getFieldMapping($field);

		return $mapping !== [] && in_array($mapping['type'], $this->validTypes, true);
	}

	/**
	 * Get the cache id
	 *
	 * @param string $className
	 *
	 * @return string
	 */
	private static function getCacheId(string $className): string
	{
		return $className . '\\$' . strtoupper(str_replace('\\', '_', __NAMESPACE__)) . '_CLASSMETADATA';
	}

}
