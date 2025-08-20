<?php

namespace n2n\util\ex;


use n2n\core\err\TriggeredError;
use n2n\core\err\ExceptionHandler;

class ExUtils {
	static function convertTriggeredErrors(\Closure $closure, int $convertableErrorLevel = E_ALL): mixed {
		$errorLevel = error_reporting();
		error_reporting($convertableErrorLevel ^ E_ALL);
		error_clear_last();
		try {
			$return = $closure();
		} finally {
			error_reporting($errorLevel);
		}

		if (!class_exists(ExceptionHandler::class)) {
			return $return;
		}

		$triggeredError = TriggeredError::last();
		if ($triggeredError !== null && 0 !== ($triggeredError->getCode() & $convertableErrorLevel)) {
			throw $triggeredError;
		}

		return $return;
	}
}