<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Fixtures;

use Nette\Application\Routers\RouteList;

class RouterFactory
{

	public static function createRouter(): RouteList
	{
		$router = new RouteList();

		$router->addRoute('articles/<action>[/<id>]', 'Article:default');

		$router->addRoute('<presenter>/<action>', 'Home:default');

		return $router;
	}

}
