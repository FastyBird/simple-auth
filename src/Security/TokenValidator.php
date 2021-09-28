<?php declare(strict_types = 1);

/**
 * TokenValidator.php
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
use Lcobucci\Clock;
use Lcobucci\JWT;
use Nette;
use Ramsey\Uuid;
use Throwable;

/**
 * JW token validator
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class TokenValidator
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
	 * @param string $token
	 *
	 * @return JWT\UnencryptedToken|null
	 */
	public function validate(
		string $token
	): ?JWT\Token {
		$configuration = JWT\Configuration::forSymmetricSigner(
			new JWT\Signer\Hmac\Sha256(),
			JWT\Signer\Key\InMemory::plainText($this->tokenSignature)
		);

		/** @var DateTimeImmutable $now */
		$now = $this->dateTimeFactory->getNow();

		$configuration->setValidationConstraints(
			new JWT\Validation\Constraint\IssuedBy($this->tokenIssuer),
			new JWT\Validation\Constraint\LooseValidAt(new Clock\FrozenClock($now)),
			new JWT\Validation\Constraint\SignedWith($configuration->signer(), JWT\Signer\Key\InMemory::plainText($this->tokenSignature))
		);

		try {
			$jwtToken = $configuration->parser()->parse($token);
			assert($jwtToken instanceof JWT\UnencryptedToken);

			$constraints = $configuration->validationConstraints();

			$claims = $jwtToken->claims();

			if (
				$configuration->validator()->validate($jwtToken, ...$constraints)
				&& $claims->has(SimpleAuth\Constants::TOKEN_CLAIM_USER)
				&& $claims->has(SimpleAuth\Constants::TOKEN_CLAIM_ROLES)
				&& Uuid\Uuid::isValid($claims->get(SimpleAuth\Constants::TOKEN_CLAIM_USER))
			) {
				return $jwtToken;
			}
		} catch (Throwable $ex) {
			throw new Exceptions\UnauthorizedAccessException('Token is not valid JWToken');
		}

		return null;
	}

}
