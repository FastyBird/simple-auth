<?php declare(strict_types = 1);

namespace Tests\Libs;

use FastyBird\WebServer\Http;
use IPub\SlimRouter;
use Psr\Http\Message;

class TestingController
{

	/**
	 * @return Http\Response
	 *
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function read(
		Message\ServerRequestInterface $request,
		SlimRouter\Http\Response $response,
	): SlimRouter\Http\Response
	{
		return $response;
	}

	/**
	 * @return Http\Response
	 *
	 * @Secured
	 * @Secured\Role(administrator)
	 */
	public function update(
		Message\ServerRequestInterface $request,
		SlimRouter\Http\Response $response,
	): SlimRouter\Http\Response
	{
		return $response;
	}

}
