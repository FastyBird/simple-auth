<?php declare(strict_types = 1);

/**
 * TokenValidator.php
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

use FastyBird\DateTimeFactory;
use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Exceptions;
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
	private $tokenSignature;

	/** @var JWT\Signer */
	private $signer;

	/** @var DateTimeFactory\DateTimeFactory */
	private $dateTimeFactory;

	public function __construct(
		string $tokenSignature,
		JWT\Signer $signer,
		DateTimeFactory\DateTimeFactory $dateTimeFactory
	) {
		$this->tokenSignature = $tokenSignature;

		$this->signer = $signer;
		$this->dateTimeFactory = $dateTimeFactory;
	}

	/**
	 * @param string $token
	 *
	 * @return JWT\Token|null
	 */
	public function validate(
		string $token
	): ?JWT\Token {
		$jwtParser = new JWT\Parser();

		try {
			$token = $jwtParser->parse($token);

			$validationData = new JWT\ValidationData($this->dateTimeFactory->getNow()->getTimestamp());

			if (
				$token->validate($validationData)
				&& $token->verify($this->signer, $this->tokenSignature)
				&& $token->hasClaim(SimpleAuth\Constants::TOKEN_CLAIM_USER)
				&& $token->hasClaim(SimpleAuth\Constants::TOKEN_CLAIM_ROLES)
				&& Uuid\Uuid::isValid($token->getClaim(SimpleAuth\Constants::TOKEN_CLAIM_USER))
			) {
				return $token;
			}

		} catch (Throwable $ex) {
			throw new Exceptions\UnauthorizedAccessException('Token is not valid JWToken');
		}

		return null;
	}

}
