<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit\Security;

use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Security;
use FastyBird\SimpleAuth\Tests\Cases\Unit\BaseTestCase;
use Nette\DI;

final class TokenValidatorTest extends BaseTestCase
{

	/**
	 * @throws DI\MissingServiceException
	 * @throws Exceptions\UnauthorizedAccess
	 */
	public function testValidateValidToken(): void
	{
		$tokenValidator = $this->container->getByType(Security\TokenValidator::class);

		// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
		$tokenString = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiIzOTVjOTU0Ni1hYjBkLTRhYmQtOTIzMy1lNTAyMmVjNzdlNTIiLCJpc3MiOiJmYl90ZXN0ZXIiLCJpYXQiOjE1ODU3NDI0MDAsInVzZXIiOiI1Nzg1OTI0Yy03NWE4LTQyYWUtOWJkZC1hNmNlNWVkYmFkYWMiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.-b8Q5qiiVcmF2o-CDhItIWK9Y7gtw1VRWLxS8AK2HTw';

		$token = $tokenValidator->validate($tokenString);

		self::assertNotNull($token);
	}

	/**
	 * @throws DI\MissingServiceException
	 * @throws Exceptions\UnauthorizedAccess
	 */
	public function testValidateInvalidSignatureToken(): void
	{
		$tokenValidator = $this->container->getByType(Security\TokenValidator::class);

		// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
		$tokenString = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI5Mzk1MWI3Ny0wYWE5LTQ4NGUtODBmZS00NWE4Yzg5YzJjOTYiLCJpc3MiOiJmYl90ZXN0ZXIiLCJpYXQiOjE1ODU3NDI0MDAsInVzZXIiOiJkZjE5NmRhZi1hMzBmLTQwMGYtOGZhYS1lYWExY2EyYTk4YWIiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.7PCy5JvYZfE0VeUvZZxKWRpoy-rd0Pxs6B4NruhYuDE';

		$token = $tokenValidator->validate($tokenString);

		self::assertNull($token);
	}

	/**
	 * @throws DI\MissingServiceException
	 * @throws Exceptions\UnauthorizedAccess
	 */
	public function testValidateExpiredToken(): void
	{
		$tokenValidator = $this->container->getByType(Security\TokenValidator::class);

		// phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
		$tokenString = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJmYl90ZXN0ZXIiLCJqdGkiOiJhMTAzZmRlYi0xOTI1LTQ3NDItYTQ0Zi1kYWYzZWNmMTY4YmIiLCJpYXQiOjE1ODMwNjQwMDAsImV4cCI6MTU4MzA3MTIwMCwidXNlciI6ImU4MWQzYTE1LTAyZDctNDljMy04MjVjLTc5YzVkMDExZDQ1MiIsInJvbGVzIjpbImFkbWluaXN0cmF0b3IiXX0.t3U1BK38dNEYj0Ah80PwDRfRkvsKxhSY_OuoZO7m_g0';

		$token = $tokenValidator->validate($tokenString);

		self::assertNull($token);
	}

}
