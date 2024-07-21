<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit\Middleware;

use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Middleware;
use FastyBird\SimpleAuth\Security;
use FastyBird\SimpleAuth\Tests\Cases\Unit\BaseTestCase;
use FastyBird\SimpleAuth\Tests\Fixtures;
use Fig\Http\Message\RequestMethodInterface;
use GuzzleHttp\Psr7;
use IPub\SlimRouter;
use Nette\DI;

final class UserMiddlewareTest extends BaseTestCase
{

	/**
	 * @throws DI\MissingServiceException
	 * @throws Exceptions\InvalidState
	 *
	 * @dataProvider signIn
	 */
	public function testAllowedPermission(string $url, string $method, string $token, string $id): void
	{
		$enforcerFactory = $this->container->getByType(Security\EnforcerFactory::class);

		$router = $this->createRouter();

		$request = new Psr7\ServerRequest(
			$method,
			$url,
			[
				'authorization' => $token,
			],
		);

		$router->handle($request);

		$user = $this->container->getByType(Security\User::class);

		self::assertSame($id, (string) $user->getId());
		self::assertSame([SimpleAuth\Constants::ROLE_ADMINISTRATOR], $user->getRoles());
		self::assertTrue($enforcerFactory->getEnforcer()->hasRoleForUser(
			$user->getId()?->toString() ?? SimpleAuth\Constants::USER_ANONYMOUS,
			SimpleAuth\Constants::ROLE_ADMINISTRATOR,
		));
	}

	/**
	 * @return array<string, array<int, string|int>>
	 */
	public static function signIn(): array
	{
		return [
			'signedInUser' => [
				'/v1/testing-endpoint',
				RequestMethodInterface::METHOD_GET,
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiIzOTVjOTU0Ni1hYjBkLTRhYmQtOTIzMy1lNTAyMmVjNzdlNTIiLCJpc3MiOiJmYl90ZXN0ZXIiLCJpYXQiOjE1ODU3NDI0MDAsInVzZXIiOiI1Nzg1OTI0Yy03NWE4LTQyYWUtOWJkZC1hNmNlNWVkYmFkYWMiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.-b8Q5qiiVcmF2o-CDhItIWK9Y7gtw1VRWLxS8AK2HTw',
				'5785924c-75a8-42ae-9bdd-a6ce5edbadac',
			],
		];
	}

	/**
	 * @throws DI\MissingServiceException
	 */
	protected function createRouter(): SlimRouter\Routing\Router
	{
		$controller = new Fixtures\Controllers\TestingController();

		$router = new SlimRouter\Routing\Router(new Psr7\HttpFactory());

		$route = $router
			->group('/v1', static function (SlimRouter\Routing\RouteCollector $group) use ($controller): void {
				$group->get('/testing-endpoint', [$controller, 'read']);
				$group->patch('/testing-endpoint', [$controller, 'update']);
			});

		$route->addMiddleware($this->container->getByType(Middleware\Access::class));
		$route->addMiddleware($this->container->getByType(Middleware\User::class));

		return $router;
	}

}
