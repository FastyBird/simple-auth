<?php declare(strict_types = 1);

/**
 * UserMiddleware.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:NodeAuth!
 * @subpackage     Middleware
 * @since          0.1.0
 *
 * @date           09.07.20
 */

namespace FastyBird\NodeAuth\Middleware;

use FastyBird\NodeAuth;
use FastyBird\NodeAuth\Security;
use Nette\Security as NS;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * User login middleware
 *
 * @package        FastyBird:NodeAuth!
 * @subpackage     Middleware
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class UserMiddleware implements MiddlewareInterface
{

	/** @var NS\User */
	private $user;

	/** @var Security\TokenReader */
	private $tokenReader;

	/** @var Security\IIdentityFactory */
	private $identityFactory;

	public function __construct(
		NS\User $user,
		Security\TokenReader $tokenReader,
		Security\IIdentityFactory $identityFactory
	) {
		$this->user = $user;

		$this->tokenReader = $tokenReader;
		$this->identityFactory = $identityFactory;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 *
	 * @return ResponseInterface
	 *
	 * @throws NS\AuthenticationException
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		// Request has to have Authorization header
		if ($request->hasHeader(NodeAuth\Constants::TOKEN_HEADER_NAME)) {
			$token = $this->tokenReader->read($request);

			if ($token !== null) {
				$identity = $this->identityFactory->create($token);

				if ($identity !== null) {
					$this->user->login($identity);

					return $handler->handle($request);
				}
			}

		}

		$this->user->logout(true);

		return $handler->handle($request);
	}

}
