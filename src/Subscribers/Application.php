<?php declare(strict_types = 1);

/**
 * Application.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Subscribers
 * @since          1.0.0
 *
 * @date           01.07.24
 */

namespace FastyBird\SimpleAuth\Subscribers;

use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Events;
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Security;
use Lcobucci\JWT;
use Nette;
use Nette\Http;
use Symfony\Component\EventDispatcher;
use Throwable;
use function is_string;

/**
 * Application UI events
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Application implements EventDispatcher\EventSubscriberInterface
{

	use Nette\SmartObject;

	public function __construct(
		private readonly Security\IIdentityFactory $identityFactory,
		private readonly Security\User $user,
		private readonly Security\TokenValidator $tokenValidator,
		private readonly Http\RequestFactory $requestFactory,
	)
	{
	}

	public static function getSubscribedEvents(): array
	{
		return [
			Events\Request::class => 'request',
		];
	}

	public function request(): void
	{
		try {
			$token = $this->getToken();

			if ($token !== null) {
				$identity = $this->identityFactory->create($token);

				if ($identity !== null) {
					$this->user->login($identity);

					return;
				}
			}
		} catch (Throwable) {
			// Just ignore it
		}

		$this->user->logout();
	}

	/**
	 * @throws Exceptions\UnauthorizedAccess
	 */
	private function getToken(): JWT\UnencryptedToken|null
	{
		$request = $this->requestFactory->fromGlobals();

		$token = $request->getCookie(SimpleAuth\Constants::ACCESS_TOKEN_COOKIE);

		if (is_string($token)) {
			$token = $this->tokenValidator->validate($token);

			if ($token === null) {
				return null;
			}

			return $token;
		}

		return null;
	}

}
