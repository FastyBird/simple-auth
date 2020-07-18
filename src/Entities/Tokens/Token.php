<?php declare(strict_types = 1);

/**
 * Token.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\NodeAuth\Entities\Tokens;

use Consistence\Doctrine\Enum\EnumAnnotation as Enum;
use Doctrine\Common;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\NodeAuth\Types;
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
 *       @ORM\Index(name="token_status_idx", columns={"token_status"})
 *     }
 * )
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="token_type", type="string", length=20)
 * @ORM\DiscriminatorMap({
 *      "token" = "FastyBird\NodeAuth\Entities\Tokens\Token"
 * })
 * @ORM\MappedSuperclass
 */
abstract class Token implements IToken
{

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="token_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected $id;

	/**
	 * @var IToken|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\ManyToOne(targetEntity="FastyBird\NodeAuth\Entities\Tokens\Token", inversedBy="children")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="token_id", nullable=true, onDelete="set null")
	 */
	protected $parent;

	/**
	 * @var Common\Collections\Collection<int, IToken>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\NodeAuth\Entities\Tokens\Token", mappedBy="parent")
	 */
	protected $children;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="token_token", type="text", nullable=false)
	 */
	protected $token;

	/**
	 * @var Types\TokenStatusType
	 *
	 * @Enum(class=Types\TokenStatusType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string_enum", name="token_status", nullable=false, options={"default": "active"})
	 */
	protected $status;

	/**
	 * @param string $token
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $token,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->token = $token;
		$this->status = Types\TokenStatusType::get(Types\TokenStatusType::STATE_ACTIVE);

		$this->children = new Common\Collections\ArrayCollection();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setParent(IToken $token): void
	{
		$this->parent = $token;

		$token->addChild($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParent(): ?IToken
	{
		return $this->parent;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeParent(): void
	{
		$this->parent = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setChildren(array $children): void
	{
		$this->children = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		/** @var IToken $entity */
		foreach ($children as $entity) {
			if (!$this->children->contains($entity)) {
				// ...and assign them to collection
				$this->children->add($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addChild(IToken $child): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->children->contains($child)) {
			// ...and assign it to collection
			$this->children->add($child);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getChildren(): array
	{
		return $this->children->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeChild(IToken $child): void
	{
		// Check if collection contain removing entity...
		if ($this->children->contains($child)) {
			// ...and remove it from collection
			$this->children->removeElement($child);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getToken(): string
	{
		return $this->token;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setStatus(Types\TokenStatusType $status): void
	{
		$this->status = $status;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStatus(): Types\TokenStatusType
	{
		return $this->status;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isActive(): bool
	{
		return $this->status === Types\TokenStatusType::get(Types\TokenStatusType::STATE_ACTIVE);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isBlocked(): bool
	{
		return $this->status === Types\TokenStatusType::get(Types\TokenStatusType::STATE_BLOCKED);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isDeleted(): bool
	{
		return $this->status === Types\TokenStatusType::get(Types\TokenStatusType::STATE_DELETED);
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getToken();
	}

}
