<?php declare(strict_types = 1);

namespace Tests\Cases\Models;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\NodeAuth\Entities;
use FastyBird\NodeAuth\Mapping\Annotation as FB;

/**
 * @ORM\Entity
 */
class ArticleEntity implements Entities\IEntityOwner
{

	use Entities\TEntityOwner;

	/**
	 * @var int
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $title;

	/**
	 * @var mixed
	 *
	 * @FB\Owner(on="create")
	 */
	protected $owner;

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title): void
	{
		$this->title = $title;
	}

	/**
	 * @param mixed $owner
	 */
	public function setOwner($owner): void
	{
		$this->owner = $owner;
	}

	/**
	 * @return mixed
	 */
	public function getOwner()
	{
		return $this->owner;
	}

}
