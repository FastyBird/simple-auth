<?php declare(strict_types = 1);

/**
 * UserMiddleware.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Middleware
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\NodeAuth\Middleware;

use FastyBird\NodeAuth;
use Nette\Security as NS;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * User login middleware
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     Middleware
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class UserMiddleware implements MiddlewareInterface
{

	/** @var NodeAuth\Auth */
	private $auth;

	public function __construct(
		NodeAuth\Auth $auth
	) {
		$this->auth = $auth;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 *
	 * @return ResponseInterface
	 *
	 * @throws NS\AuthenticationException
	 */
	public function process(
		ServerRequestInterface $request,
		RequestHandlerInterface $handler
	): ResponseInterface {
		$this->auth->login($request);

		return $handler->handle($request);
	}

}
