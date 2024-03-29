<?php

namespace n2n\util\crypt;

use n2n\util\ex\IllegalStateException;

class TokenUtils {


	/**
	 * @see https://www.uuidgenerator.net/dev-corner/php
	 *
	 * @param string|null $data
	 * @return string
	 */
	static function uuidv4(string $data = null): string {
		// Generate 16 bytes (128 bits) of random data or use the data passed into the function.
		$data = $data ?? IllegalStateException::try(fn () => random_bytes(16));

		if (strlen($data) !== 16) {
			throw new \InvalidArgumentException('Random data must have length 16.');
		}

		// Set version to 0100
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		// Set bits 6-7 to 10
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		// Output the 36 character UUID.
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	static function randomToken(int $bytesNum = 16): string {
		return base_convert(bin2hex(IllegalStateException::try(fn () => random_bytes($bytesNum))),16,36);
	}
}