<?php declare(strict_types = 1);

use Fig\Http\Message\RequestMethodInterface;

return [
	'withoutToken' => [
		'/v1/testing-endpoint',
		RequestMethodInterface::METHOD_GET,
	],
];
