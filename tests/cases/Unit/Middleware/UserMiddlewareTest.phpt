<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Middleware;
use FastyBird\SimpleAuth\Security;
use IPub\SlimRouter;
use React\Http;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

require_once __DIR__ . '/../../../libs/controllers/TestingController.php';

/**
 * @testCase
 */
final class UserMiddlewareTest extends BaseTestCase
{

	/**
	 * @param string $url
	 * @param string $method
	 * @param string $token
	 * @param string $id
	 *
	 * @dataProvider ./../../../fixtures/Middleware/signIn.php
	 */
	public function testAllowedPermission(string $url, string $method, string $token, string $id): void
	{
		$router = $this->createRouter();

		$request = new Http\Message\ServerRequest(
			$method,
			$url,
			[
				'authorization' => $token,
			]
		);

		$router->handle($request);

		/** @var Security\User $user */
		$user = $this->container->getByType(Security\User::class);

		Assert::same($id, (string) $user->getId());
		Assert::same([
			SimpleAuth\Constants::ROLE_ADMINISTRATOR,
		], $user->getRoles());
	}

	/**
	 * @return SlimRouter\Routing\Router
	 */
	protected function createRouter(): SlimRouter\Routing\Router
	{
		$controller = new Controllers\TestingController();

		$router = new SlimRouter\Routing\Router();

		$route = $router
			->group('/v1', function (SlimRouter\Routing\RouteCollector $group) use ($controller): void {
				$group->get('/testing-endpoint', [$controller, 'read']);
				$group->patch('/testing-endpoint', [$controller, 'update']);
			});

		$route->addMiddleware($this->container->getByType(Middleware\AccessMiddleware::class));
		$route->addMiddleware($this->container->getByType(Middleware\UserMiddleware::class));

		return $router;
	}

}

$test_case = new UserMiddlewareTest();
$test_case->run();
