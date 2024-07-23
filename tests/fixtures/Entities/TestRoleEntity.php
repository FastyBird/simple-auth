<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Fixtures\Entities;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\SimpleAuth\Entities;
use FastyBird\SimpleAuth\Types;
use Ramsey\Uuid;

#[ORM\Entity]
class TestRoleEntity extends Entities\Policies\Policy
{

	public const TYPE = 'test_role';

	public function __construct(Uuid\UuidInterface|null $id = null)
	{
		parent::__construct(Types\PolicyType::ROLE, $id);
	}

}
