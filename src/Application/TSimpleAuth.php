<?php declare(strict_types = 1);

/**
 * TSimpleAuth.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           01.07.24
 */

namespace FastyBird\SimpleAuth\Application;

use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Security;
use Nette\Application;
use ReflectionClass;
use ReflectionMethod;

/**
 * Nette's presenters security trait
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @method Application\IPresenter getPresenter()
 * @method string storeRequest(string $expiration = '+ 10 minutes')
 *
 * @property-read Security\User|null $user;
 */
trait TSimpleAuth
{

	protected SimpleAuth\Configuration $simpleAuthConfiguration;

	protected Security\AnnotationChecker $annotationChecker;

	public function injectSimpleAuth(
		Security\AnnotationChecker $annotationChecker,
		SimpleAuth\Configuration $configuration,
	): void
	{
		$this->annotationChecker = $annotationChecker;
		$this->simpleAuthConfiguration = $configuration;
	}

	/**
	 * @param mixed $element
	 *
	 * @throws Application\ForbiddenRequestException
	 * @throws Application\UI\InvalidLinkException
	 */
	public function checkRequirements(ReflectionClass|ReflectionMethod $element): void
	{
		$redirectUrl = $this->simpleAuthConfiguration->getRedirectUrl([
			'backlink' => $this->storeRequest(),
		]);

		try {
			parent::checkRequirements($element);

			if (!$this->annotationChecker->checkAccess(
				$this->user,
				$element->class,
				$element instanceof ReflectionMethod ? $element->name : null,
			)) {
				throw new Application\ForbiddenRequestException();
			}
		} catch (Application\ForbiddenRequestException $ex) {
			if ($redirectUrl) {
				$this->getPresenter()->redirectUrl($redirectUrl);

			} else {
				throw $ex;
			}
		}
	}

}
