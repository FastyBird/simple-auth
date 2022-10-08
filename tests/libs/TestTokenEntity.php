<?php declare(strict_types = 1);

namespace Tests\Libs;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\SimpleAuth\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_security_tokens_test",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Testing tokens"
 *     }
 * )
 */
class TestTokenEntity extends Entities\Tokens\Token
{

	/**
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="string", nullable=true)
	 */
	private string $content;

	public function getContent(): string
	{
		return $this->content;
	}

	public function setContent(string $content): void
	{
		$this->content = $content;
	}

}
