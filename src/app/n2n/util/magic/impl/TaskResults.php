<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\util\magic\impl;

use n2n\util\magic\TaskResult;
use n2n\util\magic\MagicArray;
use n2n\util\ex\IllegalStateException;

class TaskResults {

	static function valid(mixed $value = null): TaskResult {
		return new class($value) implements TaskResult {
			function __construct(private mixed $value) {
			}

			function isValid(): bool {
				return true;
			}

			/**
			 * @deprecated legacy usage only
			 */
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

	static function invalid(MagicArray $errorMap): TaskResult {
		return new class($errorMap) implements TaskResult {
			function __construct(private MagicArray $errorMap) {
			}

			function isValid(): bool {
				return false;
			}

			/**
			 * @deprecated legacy usage only
			 */
			function hasErrors(): bool {
				return true;
			}

			function getErrorMap(): MagicArray {
				return $this->errorMap;
			}

			function get(): mixed {
				throw new IllegalStateException('TaskResult is invalid.');
			}
		};
	}

}