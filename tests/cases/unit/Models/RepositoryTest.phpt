<?php declare(strict_types = 1);

namespace Tests\Cases\Unit;

use FastyBird\SimpleAuth\Models;
use FastyBird\SimpleAuth\Queries;
use Nette\Utils;
use Tester\Assert;
use Tests\Fixtures;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

require_once __DIR__ . '/../../../fixtures/Entities/TestTokenEntity.php';

/**
 * @testCase
 */
final class RepositoryTest extends BaseTestCase
{

	public function testTokenEntity(): void
	{
		$this->generateDbSchema();

		/** @var Models\Tokens\TokensManager $manager */
		$manager = $this->container->getByType(Models\Tokens\TokensManager::class);

		/** @var Models\Tokens\TokenRepository $repository */
		$repository = $this->container->getByType(Models\Tokens\TokenRepository::class);

		$tokenEntity = $manager->create(Utils\ArrayHash::from([
			'entity'  => Fixtures\TestTokenEntity::class,
			'content' => 'Testing content',
			'token'   => 'tokenString',
		]));

		Assert::type(Fixtures\TestTokenEntity::class, $tokenEntity);
		Assert::same('tokenString', $tokenEntity->getToken());
		Assert::same('Testing content', $tokenEntity->getContent());

		$findToken = new Queries\FindTokens();
		$findToken->byToken('tokenString');

		$foundToken = $repository->findOneBy($findToken);

		Assert::type(Fixtures\TestTokenEntity::class, $foundToken);
		Assert::same('tokenString', $foundToken->getToken());

		$updatedTokenEntity = $manager->update($tokenEntity, Utils\ArrayHash::from([
			'content' => 'Updated content',
			'token'   => 'newTokenString',
		]));

		Assert::type(Fixtures\TestTokenEntity::class, $updatedTokenEntity);
		Assert::same('tokenString', $updatedTokenEntity->getToken());
		Assert::same('Updated content', $updatedTokenEntity->getContent());

		$result = $manager->delete($tokenEntity);

		Assert::true($result);

		$findToken = new Queries\FindTokens();
		$findToken->byToken('tokenString');

		$foundToken = $repository->findOneBy($findToken);

		Assert::null($foundToken);
	}

}

$test_case = new RepositoryTest();
$test_case->run();
