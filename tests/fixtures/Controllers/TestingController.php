<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Fixtures\Controllers;

use Psr\Http\Message;

class TestingController
{

	/**
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function read(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response,
	): Message\ResponseInterface
	{
		return $response;
	}

	/**
	 * @Secured
	 * @Secured\Role(administrator)
	 */
	public function update(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response,
	): Message\ResponseInterface
	{
		return $response;
	}

}
