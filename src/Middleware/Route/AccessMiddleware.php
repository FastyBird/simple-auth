<?php declare(strict_types = 1);

/**
 * AccessMiddleware.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Middleware
 * @since          0.1.0
 *
 * @date           01.04.20
 */

namespace FastyBird\NodeAuth\Middleware\Route;

use FastyBird\NodeAuth\Exceptions;
use FastyBird\NodeAuth\Security;
use IPub\SlimRouter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionException;
use Reflector;

/**
 * Access check middleware
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     Middleware
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class AccessMiddleware implements MiddlewareInterface
{

	/** @var Security\User */
	private $user;

	public function __construct(
		Security\User $user
	) {
		$this->user = $user;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 *
	 * @return ResponseInterface
	 *
	 * @throws Exceptions\ForbiddenAccessException
	 * @throws ReflectionException
	 */
	public function process(
		ServerRequestInterface $request,
		RequestHandlerInterface $handler
	): ResponseInterface {
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
				if (!$this->checkAccess(get_class($routeCallable[0]), $routeCallable[1])) {
					throw new Exceptions\ForbiddenAccessException('Access to this action is not allowed');
				}
			}
		}

		return $handler->handle($request);
	}


	/**
	 * @param string $controllerClass
	 * @param string $controllerMethod
	 *
	 * @return bool
	 *
	 * @throws ReflectionException
	 */
	private function checkAccess(
		string $controllerClass,
		string $controllerMethod
	): bool {
		if (class_exists($controllerClass)) {
			$reflection = new ReflectionClass($controllerClass);

			foreach ([$reflection, $reflection->getMethod($controllerMethod)] as $element) {
				if (!$this->isAllowed($element)) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param Reflector $element
	 *
	 * @return bool
	 */
	public function isAllowed(Reflector $element): bool
	{
		// Check annotations only if element have to be secured
		if ($this->parseAnnotation($element, 'Secured') !== null) {
			return $this->checkUser($element) && $this->checkRoles($element);

		} else {
			return true;
		}
	}

	/**
	 * @param Reflector $element
	 *
	 * @return bool
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	private function checkUser(Reflector $element): bool
	{
		// Check if element has @Secured\User annotation
		if ($this->parseAnnotation($element, 'Secured\User') !== null) {
			// Get user annotation
			$result = $this->parseAnnotation($element, 'Secured\User');

			if (count($result) > 0) {
				$user = end($result);

				// Annotation is single string
				if (in_array($user, ['loggedIn', 'guest'], true)) {
					// User have to be logged in and is not
					if ($user === 'loggedIn' && $this->user->isLoggedIn() === false) {
						return false;

						// User have to be logged out and is logged in
					} elseif ($user === 'guest' && $this->user->isLoggedIn() === true) {
						return false;
					}

					// Annotation have wrong definition
				} else {
					throw new Exceptions\InvalidArgumentException('In @Security\User annotation is allowed only one from two strings: \'loggedIn\' & \'guest\'');
				}
			}

			return true;
		}

		return true;
	}

	/**
	 * @param Reflector $element
	 *
	 * @return bool
	 */
	private function checkRoles(Reflector $element): bool
	{
		// Check if element has @Secured\Role annotation
		if ($this->parseAnnotation($element, 'Secured\Role') !== null) {
			$roles = $this->parseAnnotation($element, 'Secured\Role');

			foreach ($roles as $role) {
				// Check if role name is defined
				if (is_bool($role) || $role === null) {
					continue;
				}

				if ($this->user->isInRole($role)) {
					return true;
				}
			}

			return false;
		}

		return true;
	}

	/**
	 * @param Reflector $ref
	 * @param string $name
	 *
	 * @return mixed[]|null
	 */
	private function parseAnnotation(Reflector $ref, string $name): ?array
	{
		$callable = [$ref, 'getDocComment'];

		if (!is_callable($callable)) {
			return null;
		}

		$result = preg_match_all(
			'#[\s*]@' . preg_quote($name, '#') . '(?:\(\s*([^)]*)\s*\)|\s|$)#',
			(string) call_user_func($callable),
			$m
		);

		if ($result === false || $result === 0) {
			return null;
		}

		static $tokens = ['true' => true, 'false' => false, 'null' => null];

		$res = [];

		foreach ($m[1] as $s) {
			$items = preg_split('#\s*,\s*#', $s, -1, PREG_SPLIT_NO_EMPTY);

			foreach ($items !== false ? $items : ['true'] as $item) {
				$tmp = strtolower($item);

				if (!array_key_exists($tmp, $tokens) && $item !== '') {
					$res[] = $item;
				}
			}
		}

		return $res;
	}

}
