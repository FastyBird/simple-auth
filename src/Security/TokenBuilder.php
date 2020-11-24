<?php declare(strict_types = 1);

/**
 * TokenBuilder.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\SimpleAuth\Security;

use DateTimeInterface;
use FastyBird\DateTimeFactory;
use FastyBird\SimpleAuth;
use Lcobucci\JWT;
use Nette;
use Ramsey\Uuid;
use Throwable;

/**
 * JW token builder
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class TokenBuilder
{

	use Nette\SmartObject;

	/** @var string */
	private $tokenSignature;

	/** @var string */
	private $tokenIssuer;

	/** @var JWT\Signer */
	private $signer;

	/** @var DateTimeFactory\DateTimeFactory */
	private $dateTimeFactory;

	public function __construct(
		string $tokenSignature,
		string $tokenIssuer,
		JWT\Signer $signer,
		DateTimeFactory\DateTimeFactory $dateTimeFactory
	) {
		$this->tokenSignature = $tokenSignature;
		$this->tokenIssuer = $tokenIssuer;

		$this->signer = $signer;
		$this->dateTimeFactory = $dateTimeFactory;
	}

	/**
	 * @param string $userId
	 * @param string[] $roles
	 * @param DateTimeInterface|null $expiration
	 *
	 * @return JWT\Token
	 *
	 * @throws Throwable
	 */
	public function build(
		string $userId,
		array $roles,
		?DateTimeInterface $expiration = null
	): JWT\Token {
		$timestamp = $this->dateTimeFactory->getNow()->getTimestamp();

		$jwtBuilder = new JWT\Builder();
		$jwtBuilder->identifiedBy(Uuid\Uuid::uuid4()->toString());
		$jwtBuilder->issuedBy($this->tokenIssuer);
		$jwtBuilder->issuedAt($timestamp);

		if ($expiration !== null) {
			$jwtBuilder->expiresAt($expiration->getTimestamp());
		}

		$jwtBuilder->withClaim(SimpleAuth\Constants::TOKEN_CLAIM_USER, $userId);
		$jwtBuilder->withClaim(SimpleAuth\Constants::TOKEN_CLAIM_ROLES, $roles);

		return $jwtBuilder->getToken($this->signer, new JWT\Signer\Key($this->tokenSignature));
	}

}
