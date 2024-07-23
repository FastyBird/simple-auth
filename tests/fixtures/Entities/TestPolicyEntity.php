<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Fixtures\Entities;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\SimpleAuth\Entities;
use FastyBird\SimpleAuth\Types;
use IPub\DoctrineCrud\Mapping\Attribute as IPubDoctrine;
use Ramsey\Uuid;

#[ORM\Entity]
class TestPolicyEntity extends Entities\Policies\Policy
{

	public const TYPE = 'test_policy';

	#[IPubDoctrine\Crud(writable: true)]
	#[ORM\Column(
		name: 'policy_parent',
		type: Uuid\Doctrine\UuidBinaryType::NAME,
		nullable: true,
		options: ['default' => null],
	)]
	protected Uuid\UuidInterface|null $parent = null;

	public function __construct(Uuid\UuidInterface|null $id = null)
	{
		parent::__construct(Types\PolicyType::POLICY, $id);
	}

	public function setParent(Uuid\UuidInterface $parent): void
	{
		$this->parent = $parent;
	}

	public function getParent(): Uuid\UuidInterface|null
	{
		return $this->parent;
	}

}
