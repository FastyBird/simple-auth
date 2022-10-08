<?php declare(strict_types = 1);

/**
 * TokensManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\SimpleAuth\Models\Tokens;

use FastyBird\SimpleAuth\Entities;
use FastyBird\SimpleAuth\Models;
use IPub\DoctrineCrud\Crud;
use Nette;
use Nette\Utils;
use function assert;

/**
 * Security tokens entities manager
 *
 * @phpstan-template TEntityClass of Entities\Tokens\Token
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Models
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class TokensManager
{

	use Nette\SmartObject;

	/** @phpstan-var Crud\IEntityCrud<TEntityClass> */
	private Crud\IEntityCrud $entityCrud;

	/**
	 * @phpstan-param Crud\IEntityCrud<TEntityClass> $entityCrud
	 */
	public function __construct(Crud\IEntityCrud $entityCrud)
	{
		// Entity CRUD for handling entities
		$this->entityCrud = $entityCrud;
	}

	public function create(Utils\ArrayHash $values): Entities\Tokens\Token
	{
		// Get entity creator
		$creator = $this->entityCrud->getEntityCreator();

		$entity = $creator->create($values);
		assert($entity instanceof Entities\Tokens\Token);

		return $entity;
	}

	public function update(
		Entities\Tokens\Token $entity,
		Utils\ArrayHash $values,
	): Entities\Tokens\Token
	{
		$entity = $this->entityCrud->getEntityUpdater()
			->update($values, $entity);
		assert($entity instanceof Entities\Tokens\Token);

		return $entity;
	}

	public function delete(Entities\Tokens\Token $entity): bool
	{
		// Delete entity from database
		return $this->entityCrud->getEntityDeleter()
			->delete($entity);
	}

}
