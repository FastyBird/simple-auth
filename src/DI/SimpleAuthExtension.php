<?php declare(strict_types = 1);

/**
 * SimpleAuthExtension.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:SimpleAuth!
 * @subpackage     DI
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\SimpleAuth\DI;

use Doctrine\Persistence;
use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Entities;
use FastyBird\SimpleAuth\Mapping;
use FastyBird\SimpleAuth\Middleware;
use FastyBird\SimpleAuth\Security;
use FastyBird\SimpleAuth\Subscribers;
use IPub\DoctrineCrud;
use Nette;
use Nette\DI;
use Nette\PhpGenerator;
use Nette\Schema;
use stdClass;
use function assert;
use function ucfirst;
use const DIRECTORY_SEPARATOR;

/**
 * Authentication helpers extension container
 *
 * @package        FastyBird:SimpleAuth!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class SimpleAuthExtension extends DI\CompilerExtension
{

	public static function register(
		Nette\Bootstrap\Configurator $config,
		string $extensionName = 'fbSimpleAuth',
	): void
	{
		$config->onCompile[] = static function (Nette\Bootstrap\Configurator $config, DI\Compiler $compiler) use ($extensionName): void {
			$compiler->addExtension($extensionName, new self());
		};
	}

	public function getConfigSchema(): Schema\Schema
	{
		return Schema\Expect::structure([
			'token' => Schema\Expect::structure([
				'issuer' => Schema\Expect::string(),
				'signature' => Schema\Expect::string('g3xHbkELpMD9LRqW4WmJkHL7kz2bdNYAQJyEuFVzR3k='),
			]),
			'enable' => Schema\Expect::structure([
				'middleware' => Schema\Expect::bool(false),
				'doctrine' => Schema\Expect::structure([
					'mapping' => Schema\Expect::bool(false),
					'models' => Schema\Expect::bool(false),
				]),
			]),
			'services' => Schema\Expect::structure([
				'identity' => Schema\Expect::bool(false),
			]),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$configuration = $this->getConfig();
		assert($configuration instanceof stdClass);

		$builder->addDefinition($this->prefix('auth'), new DI\Definitions\ServiceDefinition())
			->setType(SimpleAuth\Auth::class);

		/**
		 * Token utilities
		 */

		$builder->addDefinition($this->prefix('token.builder'), new DI\Definitions\ServiceDefinition())
			->setType(Security\TokenBuilder::class)
			->setArgument('tokenSignature', $configuration->token->signature)
			->setArgument('tokenIssuer', $configuration->token->issuer);

		$builder->addDefinition($this->prefix('token.reader'), new DI\Definitions\ServiceDefinition())
			->setType(Security\TokenReader::class);

		$builder->addDefinition($this->prefix('token.validator'), new DI\Definitions\ServiceDefinition())
			->setType(Security\TokenValidator::class)
			->setArgument('tokenSignature', $configuration->token->signature)
			->setArgument('tokenIssuer', $configuration->token->issuer);

		/**
		 * User security
		 */

		if ($configuration->services->identity) {
			$builder->addDefinition($this->prefix('security.identityFactory'), new DI\Definitions\ServiceDefinition())
				->setType(Security\IdentityFactory::class);
		}

		$builder->addDefinition($this->prefix('security.userStorage'), new DI\Definitions\ServiceDefinition())
			->setType(Security\UserStorage::class);

		$builder->addDefinition($this->prefix('security.annotationChecker'), new DI\Definitions\ServiceDefinition())
			->setType(Security\AnnotationChecker::class);

		/**
		 * Web server extension
		 */

		if ($configuration->enable->middleware) {
			$builder->addDefinition($this->prefix('middleware.access'), new DI\Definitions\ServiceDefinition())
				->setType(Middleware\Access::class);

			$builder->addDefinition($this->prefix('middleware.user'), new DI\Definitions\ServiceDefinition())
				->setType(Middleware\User::class);
		}

		/**
		 * Doctrine extension
		 */

		if ($configuration->enable->doctrine->mapping) {
			$builder->addDefinition($this->prefix('doctrine.driver'), new DI\Definitions\ServiceDefinition())
				->setType(Mapping\Driver\Owner::class);

			$builder->addDefinition($this->prefix('doctrine.subscriber'), new DI\Definitions\ServiceDefinition())
				->setType(Subscribers\User::class);
		}

		if ($configuration->enable->doctrine->models) {
			$builder->addDefinition($this->prefix('doctrine.tokenRepository'), new DI\Definitions\ServiceDefinition())
				->setType(SimpleAuth\Models\Tokens\TokenRepository::class);

			$builder->addDefinition($this->prefix('doctrine.tokensManager'), new DI\Definitions\ServiceDefinition())
				->setType(SimpleAuth\Models\Tokens\TokensManager::class)
				->setArgument('entityCrud', '__placeholder__');
		}
	}

	public function beforeCompile(): void
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();
		$configuration = $this->getConfig();
		assert($configuration instanceof stdClass);

		$userContextServiceName = $builder->getByType(Security\User::class);

		$userContext = null;

		if ($userContextServiceName !== null) {
			$userContext = $builder->getDefinition($userContextServiceName);
		}

		if ($userContext === null) {
			$builder->addDefinition($this->prefix('security.user'), new DI\Definitions\ServiceDefinition())
				->setType(Security\User::class);
		}

		if ($configuration->enable->doctrine->models) {
			$ormAttributeDriverService = $builder->getDefinition('nettrineOrmAttributes.attributeDriver');

			if ($ormAttributeDriverService instanceof DI\Definitions\ServiceDefinition) {
				$ormAttributeDriverService->addSetup(
					'addPaths',
					[[__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Entities']],
				);
			}

			$ormAttributeDriverChainService = $builder->getDefinitionByType(
				Persistence\Mapping\Driver\MappingDriverChain::class,
			);

			if ($ormAttributeDriverChainService instanceof DI\Definitions\ServiceDefinition) {
				$ormAttributeDriverChainService->addSetup('addDriver', [
					$ormAttributeDriverService,
					'FastyBird\SimpleAuth\Entities',
				]);
			}
		}
	}

	public function afterCompile(PhpGenerator\ClassType $class): void
	{
		$builder = $this->getContainerBuilder();
		$configuration = $this->getConfig();
		assert($configuration instanceof stdClass);

		if ($configuration->enable->doctrine->models) {
			$entityFactoryServiceName = $builder->getByType(DoctrineCrud\Crud\IEntityCrudFactory::class, true);

			$tokensManagerService = $class->getMethod(
				'createService' . ucfirst($this->name) . '__doctrine__tokensManager',
			);
			$tokensManagerService->setBody(
				'return new ' . SimpleAuth\Models\Tokens\TokensManager::class
				. '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Tokens\Token::class . '\'));',
			);
		}
	}

}
