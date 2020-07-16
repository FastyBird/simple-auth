<?php declare(strict_types = 1);

/**
 * TokenBuilder.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\NodeAuth\Security;

use DateTimeImmutable;
use FastyBird\NodeAuth;
use FastyBird\NodeAuth\Exceptions;
use FastyBird\NodeAuth\User;
use FastyBird\NodeLibs\Helpers as NodeLibsHelpers;
use Lcobucci\JWT;
use Nette;
use Ramsey\Uuid;

/**
 * JW token builder
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class TokenBuilder
{

	use Nette\SmartObject;

	private const ACCESS_TOKEN_EXPIRATION = '+6 hours';
	private const REFRESH_TOKEN_EXPIRATION = '+3 days';

	/** @var string */
	private $tokenSignature;

	/** @var JWT\Signer */
	private $signer;

	/** @var NodeLibsHelpers\IDateFactory */
	private $dateTimeFactory;

	public function __construct(
		string $tokenSignature,
		JWT\Signer $signer,
		NodeLibsHelpers\IDateFactory $dateTimeFactory
	) {
		$this->tokenSignature = $tokenSignature;

		$this->signer = $signer;
		$this->dateTimeFactory = $dateTimeFactory;
	}

	/**
	 * @param string $type
	 * @param User\IUser $user
	 *
	 * @return JWT\Token
	 */
	public function build(
		string $type,
		User\IUser $user
	): JWT\Token {
		if (!in_array($type, [NodeAuth\Constants::TOKEN_TYPE_ACCESS, NodeAuth\Constants::TOKEN_TYPE_REFRESH], true)) {
			throw new Exceptions\InvalidStateException('Provided token type is not valid type.');
		}

		$timestamp = $this->dateTimeFactory->getNow()->getTimestamp();

		$jwtBuilder = new JWT\Builder();
		$jwtBuilder->issuedAt($timestamp);

		if ($type === NodeAuth\Constants::TOKEN_TYPE_ACCESS) {
			$jwtBuilder->expiresAt($this->getNow()->modify(self::ACCESS_TOKEN_EXPIRATION)->getTimestamp());

		} else {
			$jwtBuilder->expiresAt($this->getNow()->modify(self::REFRESH_TOKEN_EXPIRATION)->getTimestamp());
		}

		$jwtBuilder->identifiedBy(Uuid\Uuid::uuid4()->toString());

		$jwtBuilder->withClaim('account', $user->getId());
		$jwtBuilder->withClaim('name', $user->getName());
		$jwtBuilder->withClaim('type', $type);

		if ($type === NodeAuth\Constants::TOKEN_TYPE_ACCESS) {
			$jwtBuilder->withClaim('roles', array_map(function ($role): string {
				return (string) $role;
			}, $user->getRoles()));
		}

		return $jwtBuilder->getToken($this->signer, new JWT\Signer\Key($this->tokenSignature));
	}

	/**
	 * @return DateTimeImmutable
	 */
	private function getNow(): DateTimeImmutable
	{
		/** @var DateTimeImmutable $now */
		$now = $this->dateTimeFactory->getNow();

		return $now;
	}

}
