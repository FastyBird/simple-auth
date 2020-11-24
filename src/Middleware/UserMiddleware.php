<?php declare(strict_types = 1);

/**
 * UserMiddleware.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Middleware
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\SimpleAuth\Middleware;

use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Exceptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * User login middleware
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Middleware
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class UserMiddleware implements MiddlewareInterface
{

	/** @var SimpleAuth\Auth */
	private $auth;

	public function __construct(
		SimpleAuth\Auth $auth
	) {
		$this->auth = $auth;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 *
	 * @return ResponseInterface
	 *
	 * @throws Exceptions\AuthenticationException
	 */
	public function process(
		ServerRequestInterface $request,
		RequestHandlerInterface $handler
	): ResponseInterface {
		$this->auth->login($request);

		return $handler->handle($request);
	}

}
