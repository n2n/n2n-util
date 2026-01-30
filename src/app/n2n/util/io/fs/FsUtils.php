<?php

namespace n2n\util\io\fs;

class FsUtils {

	const BYTES_PER_KB = 1024;
	const BYTES_PER_MB = 1024 * 1024;
	const BYTES_PER_GB = 1024 * 1024 * 1024;

	static function prettyFileSize(int $bytes, bool $roundUpDecimals = false): string {
		$units = [
				'GB' => self::BYTES_PER_GB,
				'MB' => self::BYTES_PER_MB,
				'KB' => self::BYTES_PER_KB
		];

		foreach ($units as $unit => $bytesPerUnit) {
			if ($bytes >= $bytesPerUnit) {
				$value = $bytes / $bytesPerUnit;
				$value = $roundUpDecimals ? ceil($value * 100) / 100 : round($value, 2);

				return $value . ' ' . $unit;
			}
		}

		return $bytes . ' B';
	}
}