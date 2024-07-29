<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Cases\Unit\Latte;

use FastyBird\SimpleAuth\Exceptions;
use FastyBird\SimpleAuth\Security;
use FastyBird\SimpleAuth\Tests\Cases\Unit\BaseTestCase;
use Nette;
use Nette\Application;
use Nette\Bridges\ApplicationLatte\DefaultTemplate;
use Nette\DI;
use Nette\Utils;

final class AccessExtensionTest extends BaseTestCase
{

	private const ROOT_DIR = __DIR__ . '/../../../../';

	private const USER_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiIzOTVjOTU0Ni1hYjBkLTRhYmQtOTIzMy1lNTAyMmVjNzdlNTIiLCJpc3MiOiJmYl90ZXN0'
	. 'ZXIiLCJpYXQiOjE1ODU3NDI0MDAsInVzZXIiOiI1Nzg1OTI0Yy03NWE4LTQyYWUtOWJkZC1hNmNlNWVkYmFkYWMiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.-b8Q5qiiVcmF2o-CD'
	. 'hItIWK9Y7gtw1VRWLxS8AK2HTw';

	/**
	 * @throws DI\MissingServiceException
	 */
	public function setUp(): void
	{
		$this->registerNeonConfigurationFile(__DIR__ . '/latte.neon');

		parent::setUp();
	}

	/**
	 * @throws DI\MissingServiceException
	 * @throws Exceptions\Authentication
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws Exceptions\UnauthorizedAccess
	 * @throws Nette\IOException
	 */
	public function testAllowedHrefMacro(): void
	{
		$presenter = $this->createPresenter('Article');

		// Create GET request
		$request = new Application\Request('Article', 'GET', ['action' => 'list']);
		// & fire presenter & catch response
		$response = $presenter->run($request);

		self::assertInstanceOf(Application\Responses\TextResponse::class, $response);

		$source = $response->getSource();

		self::assertInstanceOf(DefaultTemplate::class, $source);

		self::assertXmlStringEqualsXmlString(
			Utils\FileSystem::read(
				self::ROOT_DIR . 'tests/fixtures/Presenters/responses/allowedHref.macro.anonymous.html',
			),
			$source->renderToString(),
		);

		$tokenValidator = $this->container->getByType(Security\TokenValidator::class);
		$identityFactory = $this->container->getByType(Security\IdentityFactory::class);
		$user = $this->container->getByType(Security\User::class);

		$token = $tokenValidator->validate(self::USER_TOKEN);

		if ($token !== null) {
			$identity = $identityFactory->create($token);

			if ($identity !== null) {
				$user->login($identity);
			}
		}

		// Create GET request
		$request = new Application\Request('Article', 'GET', ['action' => 'list']);
		// & fire presenter & catch response
		$response = $presenter->run($request);

		self::assertInstanceOf(Application\Responses\TextResponse::class, $response);

		$source = $response->getSource();

		self::assertInstanceOf(DefaultTemplate::class, $source);

		self::assertXmlStringEqualsXmlString(
			Utils\FileSystem::read(
				self::ROOT_DIR . 'tests/fixtures/Presenters/responses/allowedHref.macro.loggedIn.html',
			),
			$source->renderToString(),
		);
	}

	/**
	 * @throws DI\MissingServiceException
	 * @throws Exceptions\Authentication
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws Exceptions\UnauthorizedAccess
	 * @throws Nette\IOException
	 */
	public function testIfAllowedMacro(): void
	{
		$presenter = $this->createPresenter('Article');

		// Create GET request
		$request = new Application\Request('Article', 'GET', ['action' => 'read', 'id' => 'one']);
		// & fire presenter & catch response
		$response = $presenter->run($request);

		self::assertInstanceOf(Application\Responses\TextResponse::class, $response);

		$source = $response->getSource();

		self::assertInstanceOf(DefaultTemplate::class, $source);

		self::assertXmlStringEqualsXmlString(
			Utils\FileSystem::read(
				self::ROOT_DIR . 'tests/fixtures/Presenters/responses/ifAllowed.macro.anonymous.html',
			),
			$source->renderToString(),
		);

		$tokenValidator = $this->container->getByType(Security\TokenValidator::class);
		$identityFactory = $this->container->getByType(Security\IdentityFactory::class);
		$user = $this->container->getByType(Security\User::class);

		$token = $tokenValidator->validate(self::USER_TOKEN);

		if ($token !== null) {
			$identity = $identityFactory->create($token);

			if ($identity !== null) {
				$user->login($identity);
			}
		}

		// Create GET request
		$request = new Application\Request('Article', 'GET', ['action' => 'read', 'id' => 'one']);
		// & fire presenter & catch response
		$response = $presenter->run($request);

		self::assertInstanceOf(Application\Responses\TextResponse::class, $response);

		$source = $response->getSource();

		self::assertInstanceOf(DefaultTemplate::class, $source);

		self::assertXmlStringEqualsXmlString(
			Utils\FileSystem::read(
				self::ROOT_DIR . 'tests/fixtures/Presenters/responses/ifAllowed.macro.loggedIn.html',
			),
			$source->renderToString(),
		);
	}

	/**
	 * @throws DI\MissingServiceException
	 */
	private function createPresenter(string $presenterName): Application\IPresenter
	{
		$presenter = $this->container->getByType(Application\IPresenterFactory::class)->createPresenter($presenterName);
		// @phpstan-ignore-next-line
		$presenter->autoCanonicalize = false;

		return $presenter;
	}

}
