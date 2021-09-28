<?php declare(strict_types = 1);

/**
 * TokenReader.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\SimpleAuth\Security;

use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Exceptions;
use Lcobucci\JWT;
use Nette;
use Psr\Http\Message\ServerRequestInterface;

/**
 * JW token reader
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class TokenReader
{

	use Nette\SmartObject;

	/** @var TokenValidator */
	private TokenValidator $tokenValidator;

	public function __construct(
		TokenValidator $tokenValidator
	) {
		$this->tokenValidator = $tokenValidator;
	}

	/**
	 * @param ServerRequestInterface $request
	 *
	 * @return JWT\UnencryptedToken|null
	 */
	public function read(ServerRequestInterface $request): ?JWT\UnencryptedToken
	{
		$headerJWT = $request->hasHeader(SimpleAuth\Constants::TOKEN_HEADER_NAME) ?
			$request->getHeader(SimpleAuth\Constants::TOKEN_HEADER_NAME) : null;

		$headerJWT = is_array($headerJWT) ? reset($headerJWT) : $headerJWT;

		if (
			is_string($headerJWT)
			&& preg_match(SimpleAuth\Constants::TOKEN_HEADER_REGEXP, $headerJWT, $matches) !== false
			&& count($matches) >= 2
		) {
			$token = $this->tokenValidator->validate($matches[1]);

			if ($token === null) {
				throw new Exceptions\UnauthorizedAccessException('Access token is not valid');
			}

			return $token;
		}

		return null;
	}

}
