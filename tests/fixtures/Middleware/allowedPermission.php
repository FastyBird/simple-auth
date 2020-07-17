<?php declare(strict_types = 1);

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;

return [
	'readAllowed' => [
		'/v1/testing-endpoint',
		RequestMethodInterface::METHOD_GET,
		'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1ODU3NDI0MDAsImV4cCI6MTU4NTc2NDAwMCwianRpIjoiNmZlOGNkY2MtNDQ0NS00OTMxLThkNWUtMjY0MDdmYjRkNWUxIiwiYWNjb3VudCI6IjIwOTg2MmNmLTFjYjctNDk1Ny05MjBmLTkzYmU3ZDA4MGQ0ZCIsIm5hbWUiOiJUZXN0ZXIiLCJ0eXBlIjoiYWNjZXNzIiwicm9sZXMiOlsiYXV0aGVudGljYXRlZCJdfQ.A6x5LTTdTlnTeysSjFFntch0rzEcvfcD3H_C4QpOzV4',
		'',
		StatusCodeInterface::STATUS_OK,
	],
];
