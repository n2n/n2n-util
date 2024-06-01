<?php

namespace n2n\util\magic\impl;

use n2n\util\magic\TaskResult;
use n2n\util\magic\MagicArray;
use n2n\util\ex\IllegalStateException;

class TaskResults {

	static function success(mixed $value = null): TaskResult {
		return new class($value) implements TaskResult {
			function __construct(private mixed $value) {
			}
			function hasErrors(): bool {
				return false;
			}

			function getErrorMap(): MagicArray {
				throw new IllegalStateException('TaskResult is valid.');
			}

			function get(): mixed {
				return $this->value;
			}
		};
	}

}