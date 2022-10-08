<?php declare(strict_types = 1);

/**
 * Access.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Middleware
 * @since          0.1.0
 *
 * @date           01.04.20
 */

namespace FastyBird\SimpleAuth\Middleware;

use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Security;
use IPub\SlimRouter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function class_exists;
use function count;
use function get_class;
use function is_array;
use function is_object;
use function is_string;

/**
 * Access check middleware
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Middleware
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Access implements MiddlewareInterface
{

	public function __construct(
		private readonly Security\User $user,
		private readonly Security\AnnotationChecker $annotationChecker,
	)
	{
	}

	/**
	 * @throws Exceptions\ForbiddenAccess
	 */
	public function process(
		ServerRequestInterface $request,
		RequestHandlerInterface $handler,
	): ResponseInterface
	{
		$route = $request->getAttribute(SlimRouter\Routing\Router::ROUTE);

		if ($route instanceof SlimRouter\Routing\IRoute) {
			$routeCallable = $route->getCallable();

			if (
				is_array($routeCallable)
				&& count($routeCallable) === 2
				&& is_object($routeCallable[0])
				&& is_string($routeCallable[1])
				&& class_exists(get_class($routeCallable[0]))
			) {
				if (!$this->annotationChecker->checkAccess(
					$this->user,
					get_class($routeCallable[0]),
					$routeCallable[1],
				)) {
					throw new Exceptions\ForbiddenAccess('Access to this action is not allowed');
				}
			}
		}

		return $handler->handle($request);
	}

}
