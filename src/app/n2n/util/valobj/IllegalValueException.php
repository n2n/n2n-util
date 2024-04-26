<?php

namespace n2n\util\valobj;

class IllegalValueException extends \Exception {

	/**
	 * @throws IllegalValueException
	 */
	public static function assertTrue($arg, string $exMessage = null): void {
		if ($arg === true) return;

		throw new IllegalValueException($exMessage ?? '');
	}
}