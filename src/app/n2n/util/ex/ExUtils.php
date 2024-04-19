<?php

namespace n2n\util\ex;

use n2n\util\ex\err\TriggeredError;

class ExUtils {

	/**
	 * Will execute the passed closure, catch all throwables and convert them to non-checked exceptions.
	 *
	 * @param \Closure $closure
	 * @return mixed
	 */
	static function try(\Closure $closure): mixed {
		return IllegalStateException::try($closure);
	}

	static function convertTriggeredErrors(\Closure $closure, int $convertableErrorLevel = E_ALL): mixed {
		$errorLevel = error_reporting();
		error_reporting($convertableErrorLevel ^ E_ALL);
		error_clear_last();
		try {
			$return = $closure();
		} finally {
			error_reporting($errorLevel);
		}

		$triggeredError = TriggeredError::last();
		if ($triggeredError !== null && 0 !== ($triggeredError->getCode() & $convertableErrorLevel)) {
			throw $triggeredError;
		}

		return $return;
	}
}