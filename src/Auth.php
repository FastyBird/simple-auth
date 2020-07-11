<?php declare(strict_types = 1);

/**
 * Auth.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     common
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\NodeAuth;

use FastyBird\NodeAuth;
use Nette;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Authentication service
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     common
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Auth
{

	use Nette\SmartObject;

	/** @var Security\TokenReader */
	private $tokenReader;

	/** @var Security\TokenValidator */
	private $tokenValidator;

	public function login(ServerRequestInterface $request)
	{
		// Request has to have Authorization header
		if ($request->hasHeader(NodeAuth\Constants::TOKEN_HEADER_NAME)) {
			$token = $this->tokenReader->read($request);

			if ($token !== null) {
				$jwToken = $this->tokenValidator->validate($token);
			}
		}
	}
}
