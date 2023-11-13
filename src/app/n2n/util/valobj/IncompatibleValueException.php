<?php

namespace n2n\util\valobj;

use n2n\util\ex\IllegalStateException;

class IncompatibleValueException extends \Exception {

	/**
	 * @throws IncompatibleValueException
	 */
	public static function assertTrue($arg, string $exMessage = null): void {
		if ($arg === true) return;

		throw new IncompatibleValueException($exMessage ?? '');
	}
}