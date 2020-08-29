<?php declare(strict_types = 1);

/**
 * NodeAuthExtension.php
 *
 * @license        More in license.md
 * @copyright      https://fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     DI
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\NodeAuth\DI;

use FastyBird\NodeAuth;
use FastyBird\NodeAuth\Entities;
use FastyBird\NodeAuth\Mapping;
use FastyBird\NodeAuth\Middleware;
use FastyBird\NodeAuth\Security;
use FastyBird\NodeAuth\Subscribers;
use IPub\DoctrineCrud;
use Lcobucci;
use Nette;
use Nette\DI;
use Nette\PhpGenerator;
use Nette\Schema;
use stdClass;

/**
 * Authentication helpers extension container
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class NodeAuthExtension extends DI\CompilerExtension
{

	/**
	 * {@inheritdoc}
	 */
	public function getConfigSchema(): Schema\Schema
	{
		return Schema\Expect::structure([
			'token'  => Schema\Expect::structure([
				'issuer'    => Schema\Expect::string(),
				'signature' => Schema\Expect::string('g3xHbkELpMD9LRqW4WmJkHL7kz2bdNYAQJyEuFVzR3k='),
			]),
			'enable' => Schema\Expect::structure([
				'middleware' => Schema\Expect::bool(false),
				'doctrine'   => Schema\Expect::structure([
					'mapping' => Schema\Expect::bool(false),
					'models'  => Schema\Expect::bool(false),
				]),
			]),
			'services' => Schema\Expect::structure([
				'identity'   => Schema\Expect::bool(false),
			]),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		/** @var stdClass $configuration */
		$configuration = $this->getConfig();

		$builder->addDefinition($this->prefix('auth'))
			->setType(NodeAuth\Auth::class);

		/**
		 * Token utilities
		 */

		$builder->addDefinition($this->prefix('token.builder'))
			->setType(Security\TokenBuilder::class)
			->setArgument('tokenSignature', $configuration->token->signature)
			->setArgument('tokenIssuer', $configuration->token->issuer);

		$builder->addDefinition($this->prefix('token.reader'))
			->setType(Security\TokenReader::class);

		$builder->addDefinition($this->prefix('token.validator'))
			->setType(Security\TokenValidator::class)
			->setArgument('tokenSignature', $configuration->token->signature);

		/**
		 * User security
		 */

		if ($configuration->services->identity) {
			$builder->addDefinition($this->prefix('security.identityFactory'))
				->setType(Security\IdentityFactory::class);
		}

		$builder->addDefinition($this->prefix('security.userStorage'))
			->setType(Security\UserStorage::class);

		$builder->addDefinition($this->prefix('security.user'))
			->setType(Security\User::class);

		/**
		 * Web server extension
		 */

		if ($configuration->enable->middleware) {
			$builder->addDefinition($this->prefix('middleware.access'))
				->setType(Middleware\Route\AccessMiddleware::class);

			$builder->addDefinition($this->prefix('middleware.user'))
				->setType(Middleware\UserMiddleware::class)
				->setTags([
					'middleware' => [
						'priority' => 30,
					],
				]);
		}

		/**
		 * Doctrine extension
		 */

		if ($configuration->enable->doctrine->mapping) {
			$builder->addDefinition($this->prefix('doctrine.driver'))
				->setType(Mapping\Driver\Owner::class);

			$builder->addDefinition($this->prefix('doctrine.subscriber'))
				->setType(Subscribers\UserSubscriber::class);
		}

		if ($configuration->enable->doctrine->models) {
			$builder->addDefinition($this->prefix('doctrine.tokenRepository'))
				->setType(NodeAuth\Models\Tokens\TokenRepository::class);

			$builder->addDefinition($this->prefix('doctrine.tokensManager'))
				->setType(NodeAuth\Models\Tokens\TokensManager::class)
				->setArgument('entityCrud', '__placeholder__');
		}

		/**
		 * JWT services
		 */

		$builder->addDefinition($this->prefix('jwt.signer'))
			->setType(Lcobucci\JWT\Signer\Hmac\Sha256::class);

		$builder->addDefinition($this->prefix('jwt.parser'))
			->setType(Lcobucci\JWT\Parser::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function afterCompile(
		PhpGenerator\ClassType $class
	): void {
		$builder = $this->getContainerBuilder();
		/** @var stdClass $configuration */
		$configuration = $this->getConfig();

		if ($configuration->enable->doctrine->models) {
			$entityFactoryServiceName = $builder->getByType(DoctrineCrud\Crud\IEntityCrudFactory::class, true);

			$tokensManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__tokensManager');
			$tokensManagerService->setBody('return new ' . NodeAuth\Models\Tokens\TokensManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Tokens\Token::class . '\'));');
		}
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 *
	 * @return void
	 */
	public static function register(
		Nette\Configurator $config,
		string $extensionName = 'nodeAuth'
	): void {
		$config->onCompile[] = function (Nette\Configurator $config, DI\Compiler $compiler) use ($extensionName): void {
			$compiler->addExtension($extensionName, new NodeAuthExtension());
		};
	}

}
