<?php declare(strict_types = 1);

/**
 * NodeAuthExtension.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     DI
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\NodeAuth\DI;

use FastyBird\NodeAuth;
use FastyBird\NodeAuth\Mapping;
use FastyBird\NodeAuth\Middleware;
use FastyBird\NodeAuth\Security;
use FastyBird\NodeAuth\Subscribers;
use Lcobucci;
use Nette;
use Nette\DI;
use Nette\Schema;
use stdClass;

/**
 * Microservice node helpers extension container
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
				'signature' => Schema\Expect::string('g3xHbkELpMD9LRqW4WmJkHL7kz2bdNYAQJyEuFVzR3k='),
			]),
			'enable' => Schema\Expect::structure([
				'middleware' => Schema\Expect::bool(false),
				'entity'     => Schema\Expect::bool(false),
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
			->setArgument('tokenSignature', $configuration->token->signature);

		$builder->addDefinition($this->prefix('token.reader'))
			->setType(Security\TokenReader::class);

		$builder->addDefinition($this->prefix('token.validator'))
			->setType(Security\TokenValidator::class)
			->setArgument('tokenSignature', $configuration->token->signature);

		/**
		 * User security
		 */

		$builder->addDefinition($this->prefix('identityFactory'))
			->setType(Security\IdentityFactory::class);

		/**
		 * Web server extension
		 */

		if ($configuration->enable->middleware) {
			$builder->addDefinition($this->prefix('middleware.access'))
				->setType(Middleware\AccessMiddleware::class);

			$builder->addDefinition($this->prefix('middleware.user'))
				->setType(Middleware\UserMiddleware::class)
				->setTags([
					'middleware' => [
						'priority' => 15,
					],
				]);
		}

		/**
		 * Doctrine extension
		 */

		if ($configuration->enable->entity) {
			$builder->addDefinition($this->prefix('doctrine.driver'))
				->setType(Mapping\Driver\Owner::class);

			$builder->addDefinition($this->prefix('doctrine.subscriber'))
				->setType(Subscribers\UserSubscriber::class);
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
