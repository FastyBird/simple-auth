<?php declare(strict_types = 1);

namespace Tests\Cases\Unit;

use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Security;
use Fig\Http\Message\RequestMethodInterface;
use React\Http;
use Tester\Assert;
use Tests\Fixtures;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

require_once __DIR__ . '/../../../fixtures/Entities/ArticleEntity.php';

/**
 * @testCase
 */
final class OwnerMappingTests extends BaseTestCase
{

	public function testCreate(): void
	{
		$this->generateDbSchema();

		/** @var SimpleAuth\Auth $auth */
		$auth = $this->container->getByType(SimpleAuth\Auth::class);

		/** @var Security\User $user */
		$user = $this->container->getByType(Security\User::class);

		$request = new Http\Message\ServerRequest(
			RequestMethodInterface::METHOD_GET,
			'/some/fake/url',
			[
				'authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiIzOTVjOTU0Ni1hYjBkLTRhYmQtOTIzMy1lNTAyMmVjNzdlNTIiLCJpc3MiOiJmYl90ZXN0ZXIiLCJpYXQiOjE1ODU3NDI0MDAsInVzZXIiOiI1Nzg1OTI0Yy03NWE4LTQyYWUtOWJkZC1hNmNlNWVkYmFkYWMiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.-b8Q5qiiVcmF2o-CDhItIWK9Y7gtw1VRWLxS8AK2HTw',
			]
		);

		$auth->login($request);

		$article = new Fixtures\ArticleEntity();

		$this->em->persist($article);
		$this->em->flush();

		Assert::same('5785924c-75a8-42ae-9bdd-a6ce5edbadac', $article->getOwnerId());
		Assert::same('5785924c-75a8-42ae-9bdd-a6ce5edbadac', (string) $user->getId());

		$request = new Http\Message\ServerRequest(
			RequestMethodInterface::METHOD_GET,
			'/some/fake/url',
			[
				'authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJmNmQwZjMwYy1mNTc4LTQyYjctYjQ1NS1kNmZhNmNhMDI0YTQiLCJpc3MiOiJmYl90ZXN0ZXIiLCJpYXQiOjE1ODU3NDI0MDAsInVzZXIiOiIyNzg0ZDc1MC1mMDg1LTQ1ODAtODUyNS00ZDYyMmZhY2U4M2QiLCJyb2xlcyI6WyJ2aXNpdG9yIl19.4V5SHla2-SRBnhH_r-AJSUX7DOJV01TIsKX9JIWQsmg',
			]
		);

		$auth->login($request);

		$article->setTitle('Updated title');

		$this->em->persist($article);
		$this->em->flush();

		Assert::same('5785924c-75a8-42ae-9bdd-a6ce5edbadac', $article->getOwnerId());
		Assert::same('2784d750-f085-4580-8525-4d622face83d', (string) $user->getId());
	}

}

$test_case = new OwnerMappingTests();
$test_case->run();
