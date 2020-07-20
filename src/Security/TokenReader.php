<?php declare(strict_types = 1);

/**
 * TokenReader.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\NodeAuth\Security;

use FastyBird\NodeAuth;
use Lcobucci\JWT;
use Nette;
use Psr\Http\Message\ServerRequestInterface;

/**
 * JW token reader
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class TokenReader
{

	use Nette\SmartObject;

	/** @var TokenValidator */
	private $tokenValidator;

	public function __construct(
		TokenValidator $tokenValidator
	) {
		$this->tokenValidator = $tokenValidator;
	}

	/**
	 * @param ServerRequestInterface $request
	 *
	 * @return JWT\Token|null
	 */
	public function read(ServerRequestInterface $request): ?JWT\Token
	{
		$headerJWT = $request->hasHeader(NodeAuth\Constants::TOKEN_HEADER_NAME) ?
			$request->getHeader(NodeAuth\Constants::TOKEN_HEADER_NAME) : null;

		$headerJWT = is_array($headerJWT) ? reset($headerJWT) : $headerJWT;

		if (
			is_string($headerJWT)
			&& preg_match(NodeAuth\Constants::TOKEN_HEADER_REGEXP, $headerJWT, $matches) !== false
		) {
			return $this->tokenValidator->validate($matches[1]);
		}

		return null;
	}

}
