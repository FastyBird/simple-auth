<?php declare(strict_types = 1);

namespace Tests\Cases;

use DateTimeImmutable;
use FastyBird\NodeAuth;
use FastyBird\NodeAuth\Middleware;
use FastyBird\NodeLibs\Helpers as NodeLibsHelpers;
use IPub\SlimRouter;
use Mockery;
use Nette;
use Nette\DI;
use Nette\Security as NS;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use React\Http\Io\ServerRequest;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

require_once __DIR__ . '/../../../libs/controllers/TestingController.php';

final class UserMiddlewareTest extends BaseMockeryTestCase
{

	/** @var DI\Container */
	private $container;

	/**
	 * {@inheritdoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->container = $this->createContainer();

		$dateTimeFactory = Mockery::mock(NodeLibsHelpers\DateFactory::class);
		$dateTimeFactory
			->shouldReceive('getNow')
			->andReturn(new DateTimeImmutable('2020-04-01T12:00:00+00:00'));

		$this->mockContainerService(
			NodeLibsHelpers\IDateFactory::class,
			$dateTimeFactory
		);
	}

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

		$request = new ServerRequest(
			$method,
			$url,
			[
				'authorization' => $token,
			]
		);

		$router->handle($request);

		/** @var NS\User $user */
		$user = $this->container->getByType(NS\User::class);

		Assert::same($id, $user->getId());
		Assert::same([
			NodeAuth\Constants::ROLE_AUTHENTICATED,
		], $user->getRoles());
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
			->addMiddleware($this->container->getByType(Middleware\AccessMiddleware::class));

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

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer(): Nette\DI\Container
	{
		$rootDir = __DIR__ . '/../../../';

		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		$config->addParameters(['container' => ['class' => 'SystemContainer_' . md5((string) time())]]);
		$config->addParameters(['appDir' => $rootDir, 'wwwDir' => $rootDir]);

		$config->addConfig(__DIR__ . '/../../../common.neon');

		NodeAuth\DI\NodeAuthExtension::register($config);

		return $config->createContainer();
	}

	/**
	 * @param string $serviceType
	 * @param object $serviceMock
	 *
	 * @return void
	 */
	protected function mockContainerService(
		string $serviceType,
		object $serviceMock
	): void {
		$foundServiceNames = $this->container->findByType($serviceType);

		foreach ($foundServiceNames as $serviceName) {
			$this->replaceContainerService($serviceName, $serviceMock);
		}
	}

	/**
	 * @param string $serviceName
	 * @param object $service
	 *
	 * @return void
	 */
	private function replaceContainerService(string $serviceName, object $service): void
	{
		$this->container->removeService($serviceName);
		$this->container->addService($serviceName, $service);
	}

}

$test_case = new UserMiddlewareTest();
$test_case->run();
