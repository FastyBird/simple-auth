<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit;

use Doctrine\ORM;
use FastyBird\SimpleAuth;
use Nette;
use Nette\DI;
use Nettrine;
use PHPUnit\Framework\TestCase;
use function file_exists;
use function in_array;
use function md5;
use function time;

abstract class BaseTestCase extends TestCase
{

	protected DI\Container $container;

	protected Nettrine\ORM\EntityManagerDecorator $em;

	/** @var array<string> */
	protected array $neonFiles = [];

	/**
	 * @throws DI\MissingServiceException
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->container = $this->createContainer();
		$this->em = $this->container->getByType(Nettrine\ORM\EntityManagerDecorator::class);
	}

	protected function createContainer(string|null $additionalConfig = null): Nette\DI\Container
	{
		$rootDir = __DIR__ . '/../../../';

		$config = new Nette\Bootstrap\Configurator();
		$config->setTempDirectory($rootDir . '/var/tmp');

		$config->addStaticParameters(['container' => ['class' => 'SystemContainer_' . md5((string) time())]]);
		$config->addStaticParameters(['appDir' => $rootDir, 'wwwDir' => $rootDir]);

		$config->addConfig(__DIR__ . '/../../common.neon');

		foreach ($this->neonFiles as $neonFile) {
			$config->addConfig($neonFile);
		}

		if ($additionalConfig !== null && file_exists($additionalConfig)) {
			$config->addConfig($additionalConfig);
		}

		SimpleAuth\DI\SimpleAuthExtension::register($config);

		return $config->createContainer();
	}

	protected function mockContainerService(
		string $serviceType,
		object $serviceMock,
	): void
	{
		$foundServiceNames = $this->container->findByType($serviceType);

		foreach ($foundServiceNames as $serviceName) {
			$this->replaceContainerService($serviceName, $serviceMock);
		}
	}

	private function replaceContainerService(string $serviceName, object $service): void
	{
		$this->container->removeService($serviceName);
		$this->container->addService($serviceName, $service);
	}

	protected function registerNeonConfigurationFile(string $file): void
	{
		if (!in_array($file, $this->neonFiles, true)) {
			$this->neonFiles[] = $file;
		}
	}

	/**
	 * @throws ORM\Tools\ToolsException
	 */
	protected function generateDbSchema(): void
	{
		/** @var list<ORM\Mapping\ClassMetadata> $metadatas */
		$metadatas = $this->em->getMetadataFactory()->getAllMetadata();
		$schemaTool = new ORM\Tools\SchemaTool($this->em);
		$schemaTool->createSchema($metadatas);
	}

}
