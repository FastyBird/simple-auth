<?php declare(strict_types = 1);

namespace Tests\Cases;

use Doctrine\ORM;
use FastyBird\NodeAuth;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../DbTestCase.php';

require_once __DIR__ . '/../../../fixtures/models/ArticleEntity.php';

final class OwnerMappingTests extends DbTestCase
{

	public function testCreate(): void
	{
		/** @var ORM\EntityManager $em */
		$em = $this->getContainer()->getByType(ORM\EntityManager::class);

		/** @var NodeAuth\Auth $auth */
		$auth = $this->getContainer()->getByType(NodeAuth\Auth::class);

		$auth->setAccessToken('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1ODU3NDI0MDAsImV4cCI6MTU4NTc2NDAwMCwianRpIjoiNGY2NzEwYTEtMzhhYS00MjY0LTljMGMtYjQ1Mjg1MTgxMjcwIiwic3ViIjoiNWU3OWVmYmYtYmQwZC01YjdjLTQ2ZWYtYmZiZGVmYmZiZDM0IiwidHlwZSI6ImFjY2VzcyIsInJvbGVzIjpbImFkbWluaXN0cmF0b3IiXX0.Ijw2E1hhDvqzyDpNExUm0vAE0IK08UeZJUcDO5QMTOI');
		$auth->login();

		$article = new Models\ArticleEntity();

		$em->persist($article);
		$em->flush();

		Assert::equal('tester', $article->getOwner());
	}

}

$test_case = new OwnerMappingTests();
$test_case->run();
