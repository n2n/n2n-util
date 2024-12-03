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
namespace n2n\util\ex\err;

use n2n\util\ex\err\impl\WarningError;
use n2n\util\ex\err\impl\NoticeError;
use n2n\util\ex\err\impl\RecoverableError;
use n2n\util\ex\err\impl\ParseError;
use n2n\util\ex\err\impl\FatalError;
use n2n\util\ex\err\impl\DeprecatedError;

abstract class TriggeredError extends \Error {
	public function __construct(string $message, ?int $code = null, ?string $fileFsPath = null,
			?int $line = null, ?\Throwable $previous = null) {
		parent::__construct($message, $code ?? 0, $previous);

		$this->file = $fileFsPath;
		$this->line = $line;
	}

	function getType(): int {
		return $this->getCode();
	}

	function isBadRequest(): bool {
		return self::isPrevBadRequestMessage($this->message);
	}

	static function create(int $type, string $errstr, string $errfile, int $errline): TriggeredError {
		return match ($type) {
//			E_ERROR, E_USER_ERROR, E_COMPILE_ERROR, E_CORE_ERROR => new FatalError($errstr, $type, $errfile, $errline),
			E_WARNING, E_USER_WARNING, E_COMPILE_WARNING, E_CORE_WARNING => new WarningError($errstr, $type, $errfile, $errline),
			E_NOTICE, E_USER_NOTICE => new NoticeError($errstr, $type, $errfile, $errline),
			E_RECOVERABLE_ERROR => new RecoverableError($errstr, $type, $errfile, $errline),
//			E_STRICT => new StrictError($errstr, $type, $errfile, $errline),
			E_PARSE => new ParseError($errstr, $type, $errfile, $errline),
			E_DEPRECATED, E_USER_DEPRECATED => new DeprecatedError($errstr, $type, $errfile, $errline),
			default => new FatalError($errstr, $type, $errfile, $errline),
		};
	}

	static function last(): ?TriggeredError {
		$lastErrData = error_get_last();

		if ($lastErrData === null) {
			return null;
		}

		return self::create($lastErrData['type'], $lastErrData['message'],
				$lastErrData['file'], $lastErrData['line']);
	}

	// 	Warning: POST Content-Length of 60582676 bytes exceeds the limit of 8388608 bytes in Unknown on line 0
	const POST_LENGTH_ERROR_MSG_PREFIX = 'POST Content-Length';
	// 	Warning: Maximum number of allowable file uploads has been exceeded in Unknown on line 0
	const UPLOAD_NUM_ERROR_MSG_PREFIX = 'Maximum number';
	// Warning: Unknown: Input variables exceeded 2. To increase the limit change max_input_vars in php.ini. in Unknown on line 0
	const INPUT_VARS_NUM_ERROR_MSG_PREFIX = 'Unknown: Input variables exceeded';

	private static function isPrevBadRequestMessage($message): bool {
		return str_starts_with($message, self::POST_LENGTH_ERROR_MSG_PREFIX)
				|| str_starts_with($message, self::UPLOAD_NUM_ERROR_MSG_PREFIX)
				|| str_starts_with($message, self::INPUT_VARS_NUM_ERROR_MSG_PREFIX);
	}
}
