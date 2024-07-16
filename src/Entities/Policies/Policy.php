<?php declare(strict_types = 1);

/**
 * Policy.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           14.07.24
 */

namespace FastyBird\SimpleAuth\Entities\Policies;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\SimpleAuth\Types;
use IPub\DoctrineCrud;
use IPub\DoctrineCrud\Mapping\Attribute as IPubDoctrine;
use Ramsey\Uuid;

#[ORM\Entity]
#[ORM\Table(
	name: 'fb_security_policies',
	indexes: [
		new ORM\Index(columns: ['p_type'], name: 'p_type_idx'),
	],
	options: [
		'collate' => 'utf8mb4_general_ci',
		'charset' => 'utf8mb4',
		'comment' => 'Casbin policies',
	],
)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'policy_type', type: 'string', length: 100)]
#[ORM\MappedSuperclass]
class Policy implements DoctrineCrud\Entities\IEntity
{

	#[ORM\Id]
	#[ORM\Column(name: 'policy_id', type: Uuid\Doctrine\UuidBinaryType::NAME)]
	#[ORM\CustomIdGenerator(class: Uuid\Doctrine\UuidGenerator::class)]
	protected Uuid\UuidInterface $id;

	#[IPubDoctrine\Crud(required: true, writable: true)]
	#[ORM\Column(
		name: 'p_type',
		type: 'string',
		nullable: false,
		enumType: Types\PolicyType::class,
	)]
	protected Types\PolicyType $type;

	#[IPubDoctrine\Crud(writable: true)]
	#[ORM\Column(name: 'v0', type: 'string', length: 150, nullable: true, options: ['default' => null])]
	protected string|null $v0 = null;

	#[IPubDoctrine\Crud(writable: true)]
	#[ORM\Column(name: 'v1', type: 'string', length: 150, nullable: true, options: ['default' => null])]
	protected string|null $v1 = null;

	#[IPubDoctrine\Crud(writable: true)]
	#[ORM\Column(name: 'v2', type: 'string', length: 150, nullable: true, options: ['default' => null])]
	protected string|null $v2 = null;

	#[IPubDoctrine\Crud(writable: true)]
	#[ORM\Column(name: 'v3', type: 'string', length: 150, nullable: true, options: ['default' => null])]
	protected string|null $v3 = null;

	#[IPubDoctrine\Crud(writable: true)]
	#[ORM\Column(name: 'v4', type: 'string', length: 150, nullable: true, options: ['default' => null])]
	protected string|null $v4 = null;

	#[IPubDoctrine\Crud(writable: true)]
	#[ORM\Column(name: 'v5', type: 'string', length: 150, nullable: true, options: ['default' => null])]
	protected string|null $v5 = null;

	public function __construct(
		Types\PolicyType $type,
		Uuid\UuidInterface|null $id = null,
	)
	{
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->type = $type;

		$this->v0 = null;
		$this->v1 = null;
		$this->v2 = null;
		$this->v3 = null;
		$this->v4 = null;
		$this->v5 = null;
	}

	public function getId(): Uuid\UuidInterface
	{
		return $this->id;
	}

	public function getType(): string
	{
		return $this->type->value;
	}

	public function getV0(): string|null
	{
		return $this->v0;
	}

	public function setV0(string|null $v0): void
	{
		$this->v0 = $v0;
	}

	public function getV1(): string|null
	{
		return $this->v1;
	}

	public function setV1(string|null $v1): void
	{
		$this->v1 = $v1;
	}

	public function getV2(): string|null
	{
		return $this->v2;
	}

	public function setV2(string|null $v2): void
	{
		$this->v2 = $v2;
	}

	public function getV3(): string|null
	{
		return $this->v3;
	}

	public function setV3(string|null $v3): void
	{
		$this->v3 = $v3;
	}

	public function getV4(): string|null
	{
		return $this->v4;
	}

	public function setV4(string|null $v4): void
	{
		$this->v4 = $v4;
	}

	public function getV5(): string|null
	{
		return $this->v5;
	}

	public function setV5(string|null $v5): void
	{
		$this->v5 = $v5;
	}

}
