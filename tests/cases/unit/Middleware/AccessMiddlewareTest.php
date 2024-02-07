<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit\Middleware;

use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Middleware;
use FastyBird\SimpleAuth\Tests\Cases\Unit\BaseTestCase;
use FastyBird\SimpleAuth\Tests\Fixtures;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Psr7;
use IPub\SlimRouter;
use Nette\DI;
use Nette\Utils;

final class AccessMiddlewareTest extends BaseTestCase
{

	/**
	 * @throws DI\MissingServiceException
	 *
	 * @dataProvider withoutToken
	 */
	public function testWithoutToken(string $url, string $method): void
	{
		self::expectException(Exceptions\ForbiddenAccess::class);
		self::expectExceptionMessage('Access to this action is not allowed');

		$router = $this->createRouter();

		$request = new Psr7\ServerRequest($method, $url);

		$router->handle($request);
	}

	/**
	 * @return array<string, array<int, string|int>>
	 */
	public static function withoutToken(): array
	{
		return [
			'withoutToken' => [
				'/v1/testing-endpoint',
				RequestMethodInterface::METHOD_GET,
			],
		];
	}

	/**
	 * @throws DI\MissingServiceException
	 *
	 * @dataProvider allowedPermission
	 */
	public function testAllowedPermission(
		string $url,
		string $method,
		string $token,
		string $body,
		int $statusCode,
	): void
	{
		$router = $this->createRouter();

		$request = new Psr7\ServerRequest(
			$method,
			$url,
			[
				'authorization' => $token,
			],
			$body,
		);

		$response = $router->handle($request);

		self::assertSame($statusCode, $response->getStatusCode());
	}

	/**
	 * @return array<string, array<int, string|int>>
	 */
	public static function allowedPermission(): array
	{
		return [
			'readAllowed' => [
				'/v1/testing-endpoint',
				RequestMethodInterface::METHOD_GET,
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiIzOTVjOTU0Ni1hYjBkLTRhYmQtOTIzMy1lNTAyMmVjNzdlNTIiLCJpc3MiOiJmYl90ZXN0ZXIiLCJpYXQiOjE1ODU3NDI0MDAsInVzZXIiOiI1Nzg1OTI0Yy03NWE4LTQyYWUtOWJkZC1hNmNlNWVkYmFkYWMiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.-b8Q5qiiVcmF2o-CDhItIWK9Y7gtw1VRWLxS8AK2HTw',
				'',
				StatusCodeInterface::STATUS_OK,
			],
		];
	}

	/**
	 * @throws DI\MissingServiceException
	 *
	 * @dataProvider deniedPermission
	 */
	public function testDeniedPermission(string $url, string $method, string $token, string $body): void
	{
		self::expectException(Exceptions\ForbiddenAccess::class);
		self::expectExceptionMessage('Access to this action is not allowed');

		$router = $this->createRouter();

		$request = new Psr7\ServerRequest(
			$method,
			$url,
			[
				'authorization' => $token,
			],
			$body,
		);

		$router->handle($request);
	}

	/**
	 * @return array<string, array<int, string|int>>
	 *
	 * @throws Utils\JsonException
	 */
	public static function deniedPermission(): array
	{
		return [
			'updateForbidden' => [
				'/v1/testing-endpoint',
				RequestMethodInterface::METHOD_PATCH,
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJmNmQwZjMwYy1mNTc4LTQyYjctYjQ1NS1kNmZhNmNhMDI0YTQiLCJpc3MiOiJmYl90ZXN0ZXIiLCJpYXQiOjE1ODU3NDI0MDAsInVzZXIiOiIyNzg0ZDc1MC1mMDg1LTQ1ODAtODUyNS00ZDYyMmZhY2U4M2QiLCJyb2xlcyI6WyJ2aXNpdG9yIl19.4V5SHla2-SRBnhH_r-AJSUX7DOJV01TIsKX9JIWQsmg',
				Utils\Json::encode([
					'update' => 'value',
				]),
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
