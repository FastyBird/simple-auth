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

use DateTimeInterface;
use FastyBird\NodeAuth\Exceptions;
use FastyBird\NodeLibs\Helpers as NodeLibsHelpers;
use Lcobucci\JWT;
use Nette;
use Nette\Security as NS;

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

	public const TOKEN_TYPE_ACCESS = 'access';
	public const TOKEN_TYPE_REFRESH = 'refresh';

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
	 * @param string $id
	 * @param string $type
	 * @param NS\User $user
	 * @param DateTimeInterface|null $expirationTime
	 *
	 * @return JWT\Token
	 */
	public function build(
		string $id,
		string $type,
		NS\User $user,
		?DateTimeInterface $expirationTime = null
	): JWT\Token {
		if (!in_array($type, [self::TOKEN_TYPE_ACCESS, self::TOKEN_TYPE_REFRESH], true)) {
			throw new Exceptions\InvalidStateException('Provided token type is not valid type.');
		}

		$timestamp = $this->dateTimeFactory->getNow()->getTimestamp();

		$jwtBuilder = new JWT\Builder();
		$jwtBuilder->issuedAt($timestamp);

		if ($expirationTime !== null) {
			$jwtBuilder->expiresAt($expirationTime->getTimestamp());
		}

		$jwtBuilder->identifiedBy($id);

		if ($user->getId() !== null) {
			$jwtBuilder->withClaim('account', $user->getId());
		}

		$jwtBuilder->withClaim('type', $type);

		if ($type === self::TOKEN_TYPE_ACCESS) {
			$jwtBuilder->withClaim('roles', array_map(function ($role): string {
				return (string) $role;
			}, $user->getRoles()));
		}

		return $jwtBuilder->getToken($this->signer, new JWT\Signer\Key($this->tokenSignature));
	}

}
