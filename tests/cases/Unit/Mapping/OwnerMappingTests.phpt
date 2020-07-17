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
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

require_once __DIR__ . '/../../../libs/models/ArticleEntity.php';

final class OwnerMappingTests extends BaseMockeryTestCase
{

	/** @var DI\Container */
	private $container;

	/** @var ORM\EntityManager */
	private $em;

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

	public function testCreate(): void
	{
		$this->generateDbSchema();

		/** @var NodeAuth\Auth $auth */
		$auth = $this->container->getByType(NodeAuth\Auth::class);

		$auth->setAccessToken('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1ODU3NDI0MDAsImV4cCI6MTU4NTc2NDAwMCwianRpIjoiN2U4NGNkNjgtZTdlYi00ZWQ3LTllOGUtZmMwOTdhODk2M2JkIiwiYWNjb3VudCI6ImJjZTdkNjYwLTEzYmQtNGQwNC1hMDkzLWZkZTAwMGRjOTdkMSIsIm5hbWUiOiJUZXN0ZXIiLCJ0eXBlIjoiYWNjZXNzIiwicm9sZXMiOlsiYWRtaW5pc3RyYXRvciJdfQ.3LJsE3SZ7KWEtxEwb2X9d2qDotsKUMKk2hU5vTKRcIE');
		$auth->login();

		$article = new Models\ArticleEntity();

		$this->em->persist($article);
		$this->em->flush();

		Assert::equal('bce7d660-13bd-4d04-a093-fde000dc97d1', $article->getOwnerId());

		$auth->setAccessToken('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1ODU3NDI0MDAsImV4cCI6MTU4NTc2NDAwMCwianRpIjoiZTVkNDU4MDMtNWQ1NS00YTk5LTg2ZGUtNzA0YzM5MjAwOTI1IiwiYWNjb3VudCI6ImJjNmU2M2ZkLWI5MzMtNGY3Ni1hMDRjLTk1ZDM4ZjRlMzQwNSIsIm5hbWUiOiJUZXN0ZXIiLCJ0eXBlIjoiYWNjZXNzIiwicm9sZXMiOlsiQWRtaW5pc3RyYXRvciJdfQ.oF_cO5DERnWHI9wLtT9Jd4Gy_qwH3-Jyu4x7qbo1d54');
		$auth->login();

		$article->setTitle('Updated title');

		$this->em->persist($article);
		$this->em->flush();

		Assert::equal('bce7d660-13bd-4d04-a093-fde000dc97d1', $article->getOwnerId());
	}


	/**
	 * @return void
	 */
	private function generateDbSchema(): void
	{
		$schema = new ORM\Tools\SchemaTool($this->em);
		$schema->createSchema($this->em->getMetadataFactory()->getAllMetadata());
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

$test_case = new OwnerMappingTests();
$test_case->run();
