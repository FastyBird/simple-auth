<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit\Mapping;

use Doctrine\ORM;
use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Security;
use FastyBird\SimpleAuth\Tests\Cases\Unit\BaseTestCase;
use FastyBird\SimpleAuth\Tests\Fixtures;
use Fig\Http\Message\RequestMethodInterface;
use GuzzleHttp\Psr7;
use Nette\DI;

final class OwnerMappingTests extends BaseTestCase
{

	/**
	 * @throws DI\MissingServiceException
	 * @throws Exceptions\Authentication
	 * @throws Exceptions\InvalidState
	 * @throws Exceptions\UnauthorizedAccess
	 * @throws ORM\Tools\ToolsException
	 */
	public function testCreate(): void
	{
		$this->generateDbSchema();

		$auth = $this->container->getByType(SimpleAuth\Auth::class);

		$user = $this->container->getByType(Security\User::class);

		$request = new Psr7\ServerRequest(
			RequestMethodInterface::METHOD_GET,
			'/some/fake/url',
			[
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiIzOTVjOTU0Ni1hYjBkLTRhYmQtOTIzMy1lNTAyMmVjNzdlNTIiLCJpc3MiOiJmYl90ZXN0ZXIiLCJpYXQiOjE1ODU3NDI0MDAsInVzZXIiOiI1Nzg1OTI0Yy03NWE4LTQyYWUtOWJkZC1hNmNlNWVkYmFkYWMiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.-b8Q5qiiVcmF2o-CDhItIWK9Y7gtw1VRWLxS8AK2HTw',
			],
		);

		$auth->login($request);

		$article = new Fixtures\Entities\ArticleEntity();

		$this->em->persist($article);
		$this->em->flush();

		self::assertSame('5785924c-75a8-42ae-9bdd-a6ce5edbadac', $article->getOwnerId());
		self::assertSame('5785924c-75a8-42ae-9bdd-a6ce5edbadac', (string) $user->getId());

		$request = new Psr7\ServerRequest(
			RequestMethodInterface::METHOD_GET,
			'/some/fake/url',
			[
				// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJmNmQwZjMwYy1mNTc4LTQyYjctYjQ1NS1kNmZhNmNhMDI0YTQiLCJpc3MiOiJmYl90ZXN0ZXIiLCJpYXQiOjE1ODU3NDI0MDAsInVzZXIiOiIyNzg0ZDc1MC1mMDg1LTQ1ODAtODUyNS00ZDYyMmZhY2U4M2QiLCJyb2xlcyI6WyJ2aXNpdG9yIl19.4V5SHla2-SRBnhH_r-AJSUX7DOJV01TIsKX9JIWQsmg',
			],
		);

		$auth->login($request);

		$article->setTitle('Updated title');

		$this->em->persist($article);
		$this->em->flush();

		self::assertSame('5785924c-75a8-42ae-9bdd-a6ce5edbadac', $article->getOwnerId());
		self::assertSame('2784d750-f085-4580-8525-4d622face83d', (string) $user->getId());
	}

}
