<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Fixtures\Entities;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\SimpleAuth\Entities;

#[ORM\Entity]
class ArticleEntity implements Entities\Owner
{

	use Entities\TOwner;

	#[ORM\Id]
	#[ORM\Column(type: 'integer')]
	#[ORM\GeneratedValue(strategy: 'AUTO')]
	private int $id;

	#[ORM\Column(type: 'string', nullable: true)]
	private string $title;

	public function getId(): int
	{
		return $this->id;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): void
	{
		$this->title = $title;
	}

}
