<?php declare(strict_types = 1);

use Fig\Http\Message\RequestMethodInterface;
use Nette\Utils;

return [
	'updateForbidden' => [
		'/v1/testing-endpoint',
		RequestMethodInterface::METHOD_PATCH,
		'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJmNmQwZjMwYy1mNTc4LTQyYjctYjQ1NS1kNmZhNmNhMDI0YTQiLCJpc3MiOiJmYl90ZXN0ZXIiLCJpYXQiOjE1ODU3NDI0MDAsInVzZXIiOiIyNzg0ZDc1MC1mMDg1LTQ1ODAtODUyNS00ZDYyMmZhY2U4M2QiLCJyb2xlcyI6WyJ2aXNpdG9yIl19.4V5SHla2-SRBnhH_r-AJSUX7DOJV01TIsKX9JIWQsmg',
		Utils\Json::encode([
			'update' => 'value',
		]),
	],
];
