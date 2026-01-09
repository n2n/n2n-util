<?php

namespace n2n\util\io\fs;

class FsUtils {

	const BYTES_PER_KB = 1024;
	const BYTES_PER_MB = 1024 * 1024;
	const BYTES_PER_GB = 1024 * 1024 * 1024;

	static function prettyFileSize(int $bytesNum): string {
		if ($bytesNum >= self::BYTES_PER_GB) {
			return round($bytesNum / self::BYTES_PER_GB, 2) . ' GB';
		}

		if ($bytesNum >= self::BYTES_PER_MB) {
			return round($bytesNum / self::BYTES_PER_MB, 2) . ' MB';
		}

		if ($bytesNum >= self::BYTES_PER_KB) {
			return round($bytesNum / self::BYTES_PER_KB, 2) . ' KB';
		}

		return $bytesNum . ' B';
	}
}