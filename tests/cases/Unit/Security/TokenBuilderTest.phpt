<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\NodeAuth;
use FastyBird\NodeAuth\Security;
use Ramsey\Uuid;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

require_once __DIR__ . '/../../../libs/controllers/TestingController.php';

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
			NodeAuth\Constants::ROLE_ADMINISTRATOR,
		];

		$token = $tokenBuilder->build($userId, $roles);

		Assert::same($userId, $token->getClaim(NodeAuth\Constants::TOKEN_CLAIM_USER));
		Assert::same([
			NodeAuth\Constants::ROLE_ADMINISTRATOR,
		], $token->getClaim(NodeAuth\Constants::TOKEN_CLAIM_ROLES));
	}

}

$test_case = new TokenBuilderTest();
$test_case->run();
