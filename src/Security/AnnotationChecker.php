<?php declare(strict_types = 1);

/**
 * AnnotationChecker.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           29.08.20
 */

namespace FastyBird\SimpleAuth\Security;

use FastyBird\SimpleAuth\Exceptions;
use ReflectionClass;
use ReflectionException;
use Reflector;

/**
 * Class security annotation checker
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class AnnotationChecker
{

	/**
	 * @param User $user
	 * @param string $controllerClass
	 * @param string $controllerMethod
	 *
	 * @return bool
	 *
	 * @phpstan-param class-string $controllerClass
	 */
	public function checkAccess(
		User $user,
		string $controllerClass,
		string $controllerMethod
	): bool {
		try {
			if (class_exists($controllerClass)) {
				$reflection = new ReflectionClass($controllerClass);

				foreach ([$reflection, $reflection->getMethod($controllerMethod)] as $element) {
					if (!$this->isAllowed($user, $element)) {
						return false;
					}
				}
			}
		} catch (ReflectionException $ex) {
			return false;
		}

		return true;
	}

	/**
	 * @param User $user
	 * @param Reflector $element
	 *
	 * @return bool
	 */
	private function isAllowed(User $user, Reflector $element): bool
	{
		// Check annotations only if element have to be secured
		if ($this->parseAnnotation($element, 'Secured') !== null) {
			return $this->checkUser($user, $element) && $this->checkRoles($user, $element);

		} else {
			return true;
		}
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
			strval(call_user_func($callable)),
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

	/**
	 * @param User $user
	 * @param Reflector $element
	 *
	 * @return bool
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	private function checkUser(User $user, Reflector $element): bool
	{
		// Check if element has @Secured\User annotation
		if ($this->parseAnnotation($element, 'Secured\User') !== null) {
			// Get user annotation
			$result = $this->parseAnnotation($element, 'Secured\User');

			if (count($result) > 0) {
				$userAnnotation = end($result);

				// Annotation is single string
				if (in_array($userAnnotation, ['loggedIn', 'guest'], true)) {
					// User have to be logged in and is not
					if ($userAnnotation === 'loggedIn' && $user->isLoggedIn() === false) {
						return false;

						// User have to be logged out and is logged in
					} elseif ($userAnnotation === 'guest' && $user->isLoggedIn() === true) {
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
	 * @param User $user
	 * @param Reflector $element
	 *
	 * @return bool
	 */
	private function checkRoles(User $user, Reflector $element): bool
	{
		// Check if element has @Secured\Role annotation
		if ($this->parseAnnotation($element, 'Secured\Role') !== null) {
			$rolesAnnotation = $this->parseAnnotation($element, 'Secured\Role');

			foreach ($rolesAnnotation as $role) {
				// Check if role name is defined
				if (is_bool($role) || $role === null) {
					continue;
				}

				if ($user->isInRole(strval($role))) {
					return true;
				}
			}

			return false;
		}

		return true;
	}

}
