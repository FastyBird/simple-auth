<?php declare(strict_types = 1);

namespace Tests\Cases;

use DateTimeImmutable;
use Doctrine\ORM;
use FastyBird\NodeAuth;
use FastyBird\NodeLibs\Helpers as NodeLibsHelpers;
use Mockery;
use Nette;
use Nette\DI;
use Nettrine;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;

abstract class BaseTestCase extends BaseMockeryTestCase
{

	/** @var DI\Container */
	protected $container;

	/** @var ORM\EntityManager */
	protected $em;

	/**
	 * {@inheritdoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->container = $this->createContainer();
		$this->em = $this->container->getByType(Nettrine\ORM\EntityManagerDecorator::class);

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
	 * @return void
	 */
	protected function generateDbSchema(): void
	{
		$schema = new ORM\Tools\SchemaTool($this->em);
		$schema->createSchema($this->em->getMetadataFactory()->getAllMetadata());
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer(): Nette\DI\Container
	{
		$rootDir = __DIR__ . '/../../';

		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		$config->addParameters(['container' => ['class' => 'SystemContainer_' . md5((string) time())]]);
		$config->addParameters(['appDir' => $rootDir, 'wwwDir' => $rootDir]);

		$config->addConfig(__DIR__ . '/../../common.neon');

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
