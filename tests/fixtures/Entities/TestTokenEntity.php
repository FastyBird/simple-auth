<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Fixtures\Entities;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\SimpleAuth\Entities;
use IPub\DoctrineCrud\Mapping\Attribute as IPubDoctrine;

#[ORM\Entity]
#[ORM\Table(
	name: 'fb_security_tokens_test',
	options: [
		'collate' => 'utf8mb4_general_ci',
		'charset' => 'utf8mb4',
		'comment' => 'Testing tokens',
	],
)]
#[ORM\DiscriminatorMap([
	self::TYPE => self::class,
])]
class TestTokenEntity extends Entities\Tokens\Token
{

	public const TYPE = 'test_token';

	#[IPubDoctrine\Crud(required: true, writable: true)]
	#[ORM\Column(type: 'string', nullable: true)]
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
