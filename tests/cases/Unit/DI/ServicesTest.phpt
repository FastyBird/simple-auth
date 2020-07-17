<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\NodeAuth;
use FastyBird\NodeAuth\Mapping;
use FastyBird\NodeAuth\Middleware;
use FastyBird\NodeAuth\Security;
use FastyBird\NodeAuth\Subscribers;
use Nette;
use Ninjify\Nunjuck\TestCase\BaseTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

final class ServicesTest extends BaseTestCase
{

	public function testServicesRegistration(): void
	{
		$container = $this->createContainer();

		Assert::notNull($container->getByType(Middleware\UserMiddleware::class));
		Assert::notNull($container->getByType(Middleware\AccessMiddleware::class));

		Assert::notNull($container->getByType(Subscribers\UserSubscriber::class));

		Assert::notNull($container->getByType(Mapping\Driver\Owner::class));

		Assert::notNull($container->getByType(Security\TokenBuilder::class));
		Assert::notNull($container->getByType(Security\TokenReader::class));
		Assert::notNull($container->getByType(Security\TokenValidator::class));
		Assert::notNull($container->getByType(Security\IdentityFactory::class));
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

}

$test_case = new ServicesTest();
$test_case->run();
