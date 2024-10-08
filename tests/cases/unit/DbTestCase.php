<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit;

use Doctrine\DBAL;
use Doctrine\ORM;
use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Exceptions;
use IPub\DoctrineCrud;
use Nette;
use Nettrine;
use Nettrine\ORM as NettrineORM;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function array_reverse;
use function fclose;
use function feof;
use function fgets;
use function file_exists;
use function fopen;
use function in_array;
use function md5;
use function rtrim;
use function set_time_limit;
use function sprintf;
use function strlen;
use function substr;
use function time;
use function trim;

abstract class DbTestCase extends TestCase
{

	protected Nette\DI\Container $container;

	protected Nettrine\ORM\EntityManagerDecorator $em;

	protected bool $isDatabaseSetUp = false;

	/** @var array<string> */
	protected array $sqlFiles = [];

	/** @var array<string> */
	protected array $neonFiles = [];

	/**
	 * @throws Exceptions\InvalidArgument
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 */
	public function setUp(): void
	{
		$this->registerDatabaseSchemaFile(__DIR__ . '/../../sql/dummy.data.sql');

		parent::setUp();

		$this->container = $this->createContainer();
		$this->em = $this->container->getByType(NettrineORM\EntityManagerDecorator::class);
	}

	/**
	 * @throws Exceptions\InvalidArgument
	 * @throws RuntimeException
	 */
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

		$this->container = $config->createContainer();

		$this->setupDatabase();

		return $this->container;
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

	protected function registerDatabaseSchemaFile(string $file): void
	{
		if (!in_array($file, $this->sqlFiles, true)) {
			$this->sqlFiles[] = $file;
		}
	}

	/**
	 * @throws Exceptions\InvalidArgument
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 */
	private function setupDatabase(): void
	{
		if (!$this->isDatabaseSetUp) {
			$db = $this->getDb();

			/** @var list<ORM\Mapping\ClassMetadata<DoctrineCrud\Entities\IEntity>> $metadata */
			$metadata = $this->getEntityManager()->getMetadataFactory()->getAllMetadata();
			$schemaTool = new ORM\Tools\SchemaTool($this->getEntityManager());

			$schemas = $schemaTool->getCreateSchemaSql($metadata);

			foreach ($schemas as $sql) {
				try {
					$db->executeStatement($sql);
				} catch (DBAL\Exception) {
					throw new RuntimeException('Database schema could not be created');
				}
			}

			foreach (array_reverse($this->sqlFiles) as $file) {
				$this->loadFromFile($db, $file);
			}

			$this->isDatabaseSetUp = true;
		}
	}

	/**
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 */
	protected function getDb(): DBAL\Connection
	{
		return $this->container->getByType(DBAL\Connection::class);
	}

	/**
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 */
	protected function getEntityManager(): NettrineORM\EntityManagerDecorator
	{
		return $this->container->getByType(NettrineORM\EntityManagerDecorator::class);
	}

	/**
	 * @throws Exceptions\InvalidArgument
	 */
	private function loadFromFile(DBAL\Connection $db, string $file): void
	{
		@set_time_limit(0); // intentionally @

		$handle = @fopen($file, 'r'); // intentionally @

		if ($handle === false) {
			throw new Exceptions\InvalidArgument(sprintf('Cannot open file "%s".', $file));
		}

		$delimiter = ';';
		$sql = '';

		while (!feof($handle)) {
			$content = fgets($handle);

			if ($content !== false) {
				$s = rtrim($content);

				if (substr($s, 0, 10) === 'DELIMITER ') {
					$delimiter = substr($s, 10);
				} elseif (substr($s, -strlen($delimiter)) === $delimiter) {
					$sql .= substr($s, 0, -strlen($delimiter));

					try {
						$db->executeQuery($sql);
						$sql = '';
					} catch (DBAL\Exception) {
						// File could not be loaded
					}
				} else {
					$sql .= $s . "\n";
				}
			}
		}

		if (trim($sql) !== '') {
			try {
				$db->executeQuery($sql);
			} catch (DBAL\Exception) {
				// File could not be loaded
			}
		}

		fclose($handle);
	}

	/**
	 * @throws RuntimeException
	 */
	protected function tearDown(): void
	{
		$this->getDb()->close();

		$this->isDatabaseSetUp = false;

		parent::tearDown();
	}

}
