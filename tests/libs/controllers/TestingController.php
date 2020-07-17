<?php declare(strict_types = 1);

namespace Tests\Cases\Controllers;

use FastyBird\NodeWebServer\Http;
use IPub\SlimRouter;
use Psr\Http\Message;

class TestingController
{

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param SlimRouter\Http\Response $response
	 *
	 * @return Http\Response
	 *
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function read(
		Message\ServerRequestInterface $request,
		SlimRouter\Http\Response $response
	): SlimRouter\Http\Response {
		return $response;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param SlimRouter\Http\Response $response
	 *
	 * @return Http\Response
	 *
	 * @Secured
	 * @Secured\Role(administrator)
	 */
	public function update(
		Message\ServerRequestInterface $request,
		SlimRouter\Http\Response $response
	): SlimRouter\Http\Response {
		return $response;
	}

}
