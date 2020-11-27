<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\SimpleAuth\Middleware;
use IPub\SlimRouter;
use React\Http;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

require_once __DIR__ . '/../../../libs/controllers/TestingController.php';

/**
 * @testCase
 */
final class AccessMiddlewareTest extends BaseTestCase
{

	/**
	 * @param string $url
	 * @param string $method
	 *
	 * @dataProvider ./../../../fixtures/Middleware/withoutToken.php
	 *
	 * @throws FastyBird\SimpleAuth\Exceptions\ForbiddenAccessException Access to this action is not allowed
	 */
	public function testWithoutToken(string $url, string $method): void
	{
		$router = $this->createRouter();

		$request = new Http\Message\ServerRequest(
			$method,
			$url
		);

		$router->handle($request);
	}

	/**
	 * @param string $url
	 * @param string $method
	 * @param string $token
	 * @param string $body
	 * @param int $statusCode
	 *
	 * @dataProvider ./../../../fixtures/Middleware/allowedPermission.php
	 */
	public function testAllowedPermission(string $url, string $method, string $token, string $body, int $statusCode): void
	{
		$router = $this->createRouter();

		$request = new Http\Message\ServerRequest(
			$method,
			$url,
			[
				'authorization' => $token,
			],
			$body
		);

		$response = $router->handle($request);

		Assert::same($statusCode, $response->getStatusCode());
		Assert::type(SlimRouter\Http\Response::class, $response);
	}

	/**
	 * @param string $url
	 * @param string $method
	 * @param string $token
	 * @param string $body
	 *
	 * @dataProvider ./../../../fixtures/Middleware/deniedPermission.php
	 *
	 * @throws FastyBird\SimpleAuth\Exceptions\ForbiddenAccessException Access to this action is not allowed
	 */
	public function testDeniedPermission(string $url, string $method, string $token, string $body): void
	{
		$router = $this->createRouter();

		$request = new Http\Message\ServerRequest(
			$method,
			$url,
			[
				'authorization' => $token,
			],
			$body
		);

		$router->handle($request);
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

$test_case = new AccessMiddlewareTest();
$test_case->run();
