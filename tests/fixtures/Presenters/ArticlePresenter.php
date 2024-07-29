<?php declare(strict_types = 1);

namespace FastyBird\SimpleAuth\Tests\Fixtures\Presenters;

use Nette\Application\UI\Presenter;

class ArticlePresenter extends Presenter
{

	/**
	 * @Secured\User(loggedIn)
	 */
	public function actionRead(string $id): void
	{
		// leave it empty
	}

}
