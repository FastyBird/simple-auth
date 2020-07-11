<?php declare(strict_types = 1);

/**
 * AccountMiddleware.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Middleware
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\NodeAuth\Middleware;

use FastyBird\NodeAuth;
use FastyBird\NodeAuth\Security;
use Nette\Security as NS;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Access token check middleware
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     Middleware
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class AccountMiddleware implements MiddlewareInterface
{

	/** @var NS\User */
	private $user;

	/** @var Security\TokenReader */
	private $tokenReader;

	/** @var Security\TokenValidator */
	private $tokenValidator;

	public function __construct(
		NS\User $user,
		Security\TokenReader $tokenReader
	) {
		$this->user = $user;

		$this->tokenReader = $tokenReader;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 *
	 * @return ResponseInterface
	 *
	 * @throws NS\AuthenticationException
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		// Request has to have Authorization header
		if ($request->hasHeader(NodeAuth\Constants::TOKEN_HEADER_NAME)) {
			$token = $this->tokenReader->read($request);

			if ($token !== null) {
				$jwToken = $this->tokenValidator->validate($token);
			}

		} else {
			$this->user->logout(true);
		}

		return $handler->handle($request);
	}

}
