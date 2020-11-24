<?php declare(strict_types = 1);

/**
 * UserSubscriber.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Subscribers
 * @since          0.1.0
 *
 * @date           14.07.20
 */

namespace FastyBird\SimpleAuth\Subscribers;

use Doctrine\Common;
use Doctrine\ORM;
use FastyBird\SimpleAuth\Mapping;
use FastyBird\SimpleAuth\Security;
use Nette;

/**
 * Doctrine entities events
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class UserSubscriber implements Common\EventSubscriber
{

	use Nette\SmartObject;

	/** @var Security\User */
	private $user;

	/** @var Mapping\Driver\Owner */
	private $driver;

	/**
	 * Register events
	 *
	 * @return string[]
	 */
	public function getSubscribedEvents(): array
	{
		return [
			ORM\Events::loadClassMetadata,
			ORM\Events::onFlush,
		];
	}

	public function __construct(
		Mapping\Driver\Owner $driver,
		Security\User $user
	) {
		$this->driver = $driver;
		$this->user = $user;
	}

	/**
	 * @param ORM\Event\LoadClassMetadataEventArgs $eventArgs
	 *
	 * @return void
	 *
	 * @throws Common\Annotations\AnnotationException
	 * @throws ORM\Mapping\MappingException
	 */
	public function loadClassMetadata(
		ORM\Event\LoadClassMetadataEventArgs $eventArgs
	): void {
		$classMetadata = $eventArgs->getClassMetadata();

		$this->driver->loadMetadataForObjectClass($eventArgs->getObjectManager(), $classMetadata);

		// Register pre persist event
		$this->registerEvent($classMetadata, ORM\Events::prePersist);
	}

	/**
	 * @param ORM\Event\OnFlushEventArgs $eventArgs
	 *
	 * @return void
	 *
	 * @throws Common\Annotations\AnnotationException
	 * @throws ORM\Mapping\MappingException
	 */
	public function onFlush(
		ORM\Event\OnFlushEventArgs $eventArgs
	): void {
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		// Check all scheduled updates
		foreach ($uow->getScheduledEntityUpdates() as $object) {
			$classMetadata = $em->getClassMetadata(get_class($object));

			$config = $this->driver->getObjectConfigurations($em, $classMetadata->getName());

			if ($config !== []) {
				$changeSet = $uow->getEntityChangeSet($object);
				$needChanges = false;

				if ($uow->isScheduledForInsert($object) && isset($config['create'])) {
					foreach ($config['create'] as $field) {
						// Field can not exist in change set, when persisting embedded document without parent for example
						$new = array_key_exists($field, $changeSet) ? $changeSet[$field][1] : false;

						if ($new === null) { // let manual values
							$needChanges = true;
							$this->updateField($uow, $object, $classMetadata, $field);
						}
					}
				}

				if ($needChanges) {
					$uow->recomputeSingleEntityChangeSet($classMetadata, $object);
				}
			}
		}
	}

	/**
	 * @param mixed $entity
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
	 *
	 * @throws Common\Annotations\AnnotationException
	 * @throws ORM\Mapping\MappingException
	 */
	public function prePersist(
		$entity,
		ORM\Event\LifecycleEventArgs $eventArgs
	): void {
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();
		$classMetadata = $em->getClassMetadata(get_class($entity));

		$config = $this->driver->getObjectConfigurations($em, $classMetadata->getName());

		if ($config !== []) {
			foreach (['create'] as $event) {
				if (isset($config[$event])) {
					$this->updateFields($config[$event], $uow, $entity, $classMetadata);
				}
			}
		}
	}

	/**
	 * @param string[] $fields
	 * @param ORM\UnitOfWork $uow
	 * @param mixed $object
	 * @param ORM\Mapping\ClassMetadata $classMetadata
	 *
	 * @return void
	 */
	private function updateFields(
		array $fields,
		ORM\UnitOfWork $uow,
		$object,
		ORM\Mapping\ClassMetadata $classMetadata
	): void {
		foreach ($fields as $field) {
			if ($classMetadata->getReflectionProperty($field)->getValue($object) === null) { // let manual values
				$this->updateField($uow, $object, $classMetadata, $field);
			}
		}
	}

	/**
	 * Updates a field
	 *
	 * @param ORM\UnitOfWork $uow
	 * @param mixed $object
	 * @param ORM\Mapping\ClassMetadata $classMetadata
	 * @param string $field
	 *
	 * @return void
	 */
	private function updateField(
		ORM\UnitOfWork $uow,
		$object,
		ORM\Mapping\ClassMetadata $classMetadata,
		string $field
	): void {
		$property = $classMetadata->getReflectionProperty($field);

		$oldValue = $property->getValue($object);
		$newValue = $this->user->getId() !== null ? $this->user->getId()->toString() : null;

		$property->setValue($object, $newValue);

		$uow->propertyChanged($object, $field, $oldValue, $newValue);
		$uow->scheduleExtraUpdate($object, [
			$field => [$oldValue, $newValue],
		]);
	}

	/**
	 * @param ORM\Mapping\ClassMetadata $classMetadata
	 * @param string $eventName
	 *
	 * @return void
	 *
	 * @throws ORM\Mapping\MappingException
	 */
	private function registerEvent(
		ORM\Mapping\ClassMetadata $classMetadata,
		string $eventName
	): void {
		if (!$this->hasRegisteredListener($classMetadata, $eventName, self::class)) {
			$classMetadata->addEntityListener($eventName, self::class, $eventName);
		}
	}

	/**
	 * @param ORM\Mapping\ClassMetadata $classMetadata
	 * @param string $eventName
	 * @param string $listenerClass
	 *
	 * @return bool
	 */
	private function hasRegisteredListener(
		ORM\Mapping\ClassMetadata $classMetadata,
		string $eventName,
		string $listenerClass
	): bool {
		if (!isset($classMetadata->entityListeners[$eventName])) {
			return false;
		}

		foreach ($classMetadata->entityListeners[$eventName] as $listener) {
			if ($listener['class'] === $listenerClass && $listener['method'] === $eventName) {
				return true;
			}
		}

		return false;
	}

}
