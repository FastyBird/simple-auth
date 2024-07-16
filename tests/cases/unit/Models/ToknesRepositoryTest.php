<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit\Models;

use Doctrine\ORM;
use FastyBird\SimpleAuth\Models;
use FastyBird\SimpleAuth\Queries;
use FastyBird\SimpleAuth\Tests\Cases\Unit\BaseTestCase;
use FastyBird\SimpleAuth\Tests\Fixtures;
use IPub\DoctrineCrud\Exceptions as DoctrineCrudExceptions;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use Nette\DI;
use Nette\Utils;

final class ToknesRepositoryTest extends BaseTestCase
{

	/**
	 * @throws DI\MissingServiceException
	 * @throws DoctrineCrudExceptions\InvalidArgumentException
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 * @throws DoctrineCrudExceptions\InvalidArgumentException
	 * @throws ORM\Tools\ToolsException
	 */
	public function testTokenEntity(): void
	{
		$this->generateDbSchema();

		$manager = $this->container->getByType(Models\Tokens\Manager::class);

		$repository = $this->container->getByType(Models\Tokens\Repository::class);

		$tokenEntity = $manager->create(Utils\ArrayHash::from([
			'entity' => Fixtures\Entities\TestTokenEntity::class,
			'content' => 'Testing content',
			'token' => 'tokenString',
		]));

		self::assertInstanceOf(Fixtures\Entities\TestTokenEntity::class, $tokenEntity);
		self::assertSame('tokenString', $tokenEntity->getToken());
		self::assertSame('Testing content', $tokenEntity->getContent());

		$findToken = new Queries\FindTokens();
		$findToken->byToken('tokenString');

		$foundToken = $repository->findOneBy($findToken);

		self::assertInstanceOf(Fixtures\Entities\TestTokenEntity::class, $foundToken);
		self::assertSame('tokenString', $foundToken->getToken());

		$updatedTokenEntity = $manager->update($tokenEntity, Utils\ArrayHash::from([
			'content' => 'Updated content',
			'token' => 'newTokenString',
		]));

		self::assertInstanceOf(Fixtures\Entities\TestTokenEntity::class, $updatedTokenEntity);
		self::assertSame('tokenString', $updatedTokenEntity->getToken());
		self::assertSame('Updated content', $updatedTokenEntity->getContent());

		$result = $manager->delete($tokenEntity);

		self::assertTrue($result);

		$findToken = new Queries\FindTokens();
		$findToken->byToken('tokenString');

		$foundToken = $repository->findOneBy($findToken);

		self::assertNull($foundToken);
	}

}
