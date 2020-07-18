<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\NodeAuth\Middleware;
use IPub\SlimRouter;
use React\Http\Io\ServerRequest;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

require_once __DIR__ . '/../../../libs/controllers/TestingController.php';

final class AccessMiddlewareTest extends BaseTestCase
{

	/**
	 * @param string $url
	 * @param string $method
	 *
	 * @dataProvider ./../../../fixtures/Middleware/withoutToken.php
	 *
	 * @throws FastyBird\NodeAuth\Exceptions\ForbiddenAccessException Access to this action is not allowed
	 */
	public function testWithoutToken(string $url, string $method): void
	{
		$router = $this->createRouter();

		$request = new ServerRequest(
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

		$request = new ServerRequest(
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
	 * @throws FastyBird\NodeAuth\Exceptions\ForbiddenAccessException Access to this action is not allowed
	 */
	public function testDeniedPermission(string $url, string $method, string $token, string $body): void
	{
		$router = $this->createRouter();

		$request = new ServerRequest(
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

		$router
			->group('/v1', function (SlimRouter\Routing\RouteCollector $group) use ($controller): void {
				$group->get('/testing-endpoint', [$controller, 'read']);
				$group->patch('/testing-endpoint', [$controller, 'update']);
			})
			->addMiddleware($this->container->getByType(Middleware\Route\AccessMiddleware::class));

		$middlewareServices = $this->container->findByTag('middleware');

		// Sort by priority
		uasort($middlewareServices, function (array $a, array $b): int {
			$p1 = $a['priority'] ?? 10;
			$p2 = $b['priority'] ?? 10;

			if ($p1 === $p2) {
				return 0;
			}

			return ($p1 < $p2) ? -1 : 1;
		});

		foreach ($middlewareServices as $middlewareService => $middlewareServiceTags) {
			$router->addMiddleware($this->container->getService($middlewareService));
		}

		return $router;
	}

}

$test_case = new AccessMiddlewareTest();
$test_case->run();
