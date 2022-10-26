<?php declare(strict_types = 1);

/**
 * Token.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\SimpleAuth\Entities\Tokens;

use Consistence\Doctrine\Enum\EnumAnnotation as Enum;
use Doctrine\Common;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\SimpleAuth\Types;
use IPub\DoctrineCrud;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_security_tokens",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Security tokens"
 *     },
 *     indexes={
 *       @ORM\Index(name="token_state_idx", columns={"token_state"})
 *     }
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="token_type", type="string", length=20)
 * @ORM\DiscriminatorMap({
 *      "token" = "FastyBird\SimpleAuth\Entities\Tokens\Token"
 * })
 * @ORM\MappedSuperclass
 */
abstract class Token implements DoctrineCrud\Entities\IEntity
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="token_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\ManyToOne(targetEntity="FastyBird\SimpleAuth\Entities\Tokens\Token", inversedBy="children")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="token_id", nullable=true, onDelete="set null")
	 */
	protected Token|null $parent = null;

	/**
	 * @var Common\Collections\Collection<int, Token>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\SimpleAuth\Entities\Tokens\Token", mappedBy="parent")
	 */
	protected Common\Collections\Collection $children;

	/** @ORM\Column(name="token_token", type="text", nullable=false) */
	protected string $token;

	/**
	 * @var Types\TokenState
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 *
	 * @Enum(class=Types\TokenState::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string_enum", name="token_state", nullable=false, options={"default": "active"})
	 */
	protected $state;

	/**
	 * @throws Throwable
	 */
	public function __construct(string $token, Uuid\UuidInterface|null $id = null)
	{
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->token = $token;
		$this->state = Types\TokenState::get(Types\TokenState::STATE_ACTIVE);

		$this->children = new Common\Collections\ArrayCollection();
	}

	public function getParent(): Token|null
	{
		return $this->parent;
	}

	public function setParent(Token $token): void
	{
		$this->parent = $token;

		$token->addChild($this);
	}

	public function addChild(Token $child): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->children->contains($child)) {
			// ...and assign it to collection
			$this->children->add($child);
		}
	}

	public function removeParent(): void
	{
		$this->parent = null;
	}

	/**
	 * @return array<Token>
	 */
	public function getChildren(): array
	{
		return $this->children->toArray();
	}

	/**
	 * @param array<Token> $children
	 */
	public function setChildren(array $children): void
	{
		$this->children = new Common\Collections\ArrayCollection();

		foreach ($children as $entity) {
			// ...and assign them to collection
			$this->children->add($entity);
		}
	}

	public function removeChild(Token $child): void
	{
		// Check if collection contain removing entity...
		if ($this->children->contains($child)) {
			// ...and remove it from collection
			$this->children->removeElement($child);
		}
	}

	public function getState(): Types\TokenState
	{
		return $this->state;
	}

	public function setState(Types\TokenState $state): void
	{
		$this->state = $state;
	}

	public function isActive(): bool
	{
		return $this->state === Types\TokenState::get(Types\TokenState::STATE_ACTIVE);
	}

	public function isBlocked(): bool
	{
		return $this->state === Types\TokenState::get(Types\TokenState::STATE_BLOCKED);
	}

	public function isDeleted(): bool
	{
		return $this->state === Types\TokenState::get(Types\TokenState::STATE_DELETED);
	}

	public function getToken(): string
	{
		return $this->token;
	}

	public function __toString(): string
	{
		return $this->getToken();
	}

}
