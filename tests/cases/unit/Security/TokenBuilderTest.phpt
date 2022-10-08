<?php declare(strict_types = 1);

namespace Tests\Cases\Unit;

use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Security;
use Ramsey\Uuid;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

/**
 * @testCase
 */
final class TokenBuilderTest extends BaseTestCase
{

	public function testCreateToken(): void
	{
		/** @var Security\TokenBuilder $tokenBuilder */
		$tokenBuilder = $this->container->getByType(Security\TokenBuilder::class);

		$userId = Uuid\Uuid::uuid4()->toString();
		$roles = [
			SimpleAuth\Constants::ROLE_ADMINISTRATOR,
		];

		$token = $tokenBuilder->build($userId, $roles);

		Assert::same($userId, $token->claims()->get(SimpleAuth\Constants::TOKEN_CLAIM_USER));
		Assert::same([
			SimpleAuth\Constants::ROLE_ADMINISTRATOR,
		], $token->claims()->get(SimpleAuth\Constants::TOKEN_CLAIM_ROLES));
	}

}

$test_case = new TokenBuilderTest();
$test_case->run();
