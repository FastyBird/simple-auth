<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit\Security;

use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Security;
use FastyBird\SimpleAuth\Tests\Cases\Unit\BaseTestCase;
use Nette\DI;
use Ramsey\Uuid;

final class TokenBuilderTest extends BaseTestCase
{

	/**
	 * @throws DI\MissingServiceException
	 */
	public function testCreateToken(): void
	{
		$tokenBuilder = $this->container->getByType(Security\TokenBuilder::class);

		$userId = Uuid\Uuid::uuid4()->toString();
		$roles = [
			SimpleAuth\Constants::ROLE_ADMINISTRATOR,
		];

		$token = $tokenBuilder->build($userId, $roles);

		self::assertSame($userId, $token->claims()->get(SimpleAuth\Constants::TOKEN_CLAIM_USER));
		self::assertSame(
			[
				SimpleAuth\Constants::ROLE_ADMINISTRATOR,
			],
			$token->claims()->get(SimpleAuth\Constants::TOKEN_CLAIM_ROLES),
		);
	}

}
