<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\NodeAuth\Security;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

require_once __DIR__ . '/../../../libs/controllers/TestingController.php';

final class TokenValidatorTest extends BaseTestCase
{

	public function testValidateValidToken(): void
	{
		/** @var Security\TokenValidator $tokenValidator */
		$tokenValidator = $this->container->getByType(Security\TokenValidator::class);

		$tokenString = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiIzOTVjOTU0Ni1hYjBkLTRhYmQtOTIzMy1lNTAyMmVjNzdlNTIiLCJpc3MiOiJmYl90ZXN0ZXIiLCJpYXQiOjE1ODU3NDI0MDAsInVzZXIiOiI1Nzg1OTI0Yy03NWE4LTQyYWUtOWJkZC1hNmNlNWVkYmFkYWMiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.-b8Q5qiiVcmF2o-CDhItIWK9Y7gtw1VRWLxS8AK2HTw';

		$token = $tokenValidator->validate($tokenString);

		Assert::notNull($token);
	}

	public function testValidateInvalidSignatureToken(): void
	{
		/** @var Security\TokenValidator $tokenValidator */
		$tokenValidator = $this->container->getByType(Security\TokenValidator::class);

		$tokenString = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI5Mzk1MWI3Ny0wYWE5LTQ4NGUtODBmZS00NWE4Yzg5YzJjOTYiLCJpc3MiOiJmYl90ZXN0ZXIiLCJpYXQiOjE1ODU3NDI0MDAsInVzZXIiOiJkZjE5NmRhZi1hMzBmLTQwMGYtOGZhYS1lYWExY2EyYTk4YWIiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.7PCy5JvYZfE0VeUvZZxKWRpoy-rd0Pxs6B4NruhYuDE';

		$token = $tokenValidator->validate($tokenString);

		Assert::null($token);
	}

}

$test_case = new TokenValidatorTest();
$test_case->run();
