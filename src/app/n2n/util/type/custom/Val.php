<?php
namespace n2n\util\type\custom;

define('N2N_UTIL_TYPE_CUSTOM_UNDEFINED', Undefined::i());

class Val {

	const Undefined = N2N_UTIL_TYPE_CUSTOM_UNDEFINED;

	static function isNullOrUndefined(mixed $arg): bool {
		return $arg === null || Undefined::is($arg);
	}
}