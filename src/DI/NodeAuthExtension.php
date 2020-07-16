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

use FastyBird\NodeAuth\Mapping;
use FastyBird\NodeAuth\Security;
use FastyBird\NodeAuth\Subscribers;
use Nette;
use Nette\DI;

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
	 * {@inheritDoc}
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('tokenBuilder'))
			->setType(Security\TokenBuilder::class);

		$builder->addDefinition($this->prefix('tokenReader'))
			->setType(Security\TokenReader::class);

		$builder->addDefinition($this->prefix('tokenValidator'))
			->setType(Security\TokenValidator::class);

		$builder->addDefinition($this->prefix('driver'))
			->setType(Mapping\Driver\Owner::class);

		$builder->addDefinition($this->prefix('subscriber'))
			->setType(Subscribers\UserSubscriber::class);
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
