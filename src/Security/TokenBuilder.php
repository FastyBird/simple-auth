<?php declare(strict_types = 1);

/**
 * TokenBuilder.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\SimpleAuth\Security;

use DateTimeImmutable;
use FastyBird\DateTimeFactory;
use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Exceptions;
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
	private string $tokenSignature;

	/** @var string */
	private string $tokenIssuer;

	/** @var DateTimeFactory\DateTimeFactory */
	private DateTimeFactory\DateTimeFactory $dateTimeFactory;

	public function __construct(
		string $tokenSignature,
		string $tokenIssuer,
		DateTimeFactory\DateTimeFactory $dateTimeFactory
	) {
		$this->tokenSignature = $tokenSignature;
		$this->tokenIssuer = $tokenIssuer;

		$this->dateTimeFactory = $dateTimeFactory;
	}

	/**
	 * @param string $userId
	 * @param string[] $roles
	 * @param DateTimeImmutable|null $expiration
	 *
	 * @return JWT\UnencryptedToken
	 *
	 * @throws Throwable
	 */
	public function build(
		string $userId,
		array $roles,
		?DateTimeImmutable $expiration = null
	): JWT\UnencryptedToken {
		$configuration = JWT\Configuration::forSymmetricSigner(
			new JWT\Signer\Hmac\Sha256(),
			JWT\Signer\Key\InMemory::plainText($this->tokenSignature)
		);

		/** @var DateTimeImmutable $now */
		$now = $this->dateTimeFactory->getNow();

		$jwtBuilder = $configuration->builder();

		$jwtBuilder->issuedBy($this->tokenIssuer);
		$jwtBuilder->identifiedBy(Uuid\Uuid::uuid4()->toString());
		$jwtBuilder->issuedAt($now);

		if ($expiration !== null) {
			$jwtBuilder->expiresAt($expiration);
		}

		$jwtBuilder->withClaim(SimpleAuth\Constants::TOKEN_CLAIM_USER, $userId);
		$jwtBuilder->withClaim(SimpleAuth\Constants::TOKEN_CLAIM_ROLES, $roles);

		return $jwtBuilder->getToken($configuration->signer(), $configuration->signingKey());
	}

}
