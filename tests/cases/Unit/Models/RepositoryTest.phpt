<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\SimpleAuth\Models;
use FastyBird\SimpleAuth\Queries;
use Nette\Utils;
use Tester\Assert;
use Tests\Cases\Models\TestTokenEntity;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

require_once __DIR__ . '/../../../libs/controllers/TestingController.php';

/**
 * @testCase
 */
final class RepositoryTest extends BaseTestCase
{

	public function testTokenEntity(): void
	{
		$this->generateDbSchema();

		/** @var Models\Tokens\ITokensManager $manager */
		$manager = $this->container->getByType(Models\Tokens\ITokensManager::class);

		/** @var Models\Tokens\ITokenRepository $repository */
		$repository = $this->container->getByType(Models\Tokens\ITokenRepository::class);

		$tokenEntity = $manager->create(Utils\ArrayHash::from([
			'entity'  => TestTokenEntity::class,
			'content' => 'Testing content',
			'token'   => 'tokenString',
		]));

		Assert::type(TestTokenEntity::class, $tokenEntity);
		Assert::same('tokenString', $tokenEntity->getToken());
		Assert::same('Testing content', $tokenEntity->getContent());

		$findToken = new Queries\FindTokensQuery();
		$findToken->byToken('tokenString');

		$foundToken = $repository->findOneBy($findToken);

		Assert::type(TestTokenEntity::class, $foundToken);
		Assert::same('tokenString', $foundToken->getToken());

		$updatedTokenEntity = $manager->update($tokenEntity, Utils\ArrayHash::from([
			'content' => 'Updated content',
			'token'   => 'newTokenString',
		]));

		Assert::type(TestTokenEntity::class, $updatedTokenEntity);
		Assert::same('tokenString', $updatedTokenEntity->getToken());
		Assert::same('Updated content', $updatedTokenEntity->getContent());

		$result = $manager->delete($tokenEntity);

		Assert::true($result);

		$findToken = new Queries\FindTokensQuery();
		$findToken->byToken('tokenString');

		$foundToken = $repository->findOneBy($findToken);

		Assert::null($foundToken);
	}

}

$test_case = new RepositoryTest();
$test_case->run();
