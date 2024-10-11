<?php

namespace n2n\util\crypt;

use n2n\util\ex\IllegalStateException;

class TokenUtils {

	/**
	 * Creates cryptographically secure random code
	 *
	 * @return string
	 */
	static function createRandom(): string {
		return bin2hex(IllegalStateException::try(fn() => random_bytes(16)));
	}


	/**
	* The UUID is generated using random bytes, with specific bits modified
	* to indicate that this is a version 4 UUID (randomly generated) and to
	* follow the correct variant.
	*
	* @return string A string representation of a UUIDv4 in the format:
	*                xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx, where:
	*                - The 13th character is always '4', indicating the version.
	*                - The 17th character is '8', '9', 'A', or 'B', indicating the variant.
	* @return string A string representation of a UUIDv4
	*
	* @throws IllegalStateException if the `random_bytes()` function fails.
	 */
	public static function uuidv4(): string {
		$data = IllegalStateException::try(fn() => random_bytes(16));

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	/**
	 * Generates a UUID version 7 according to the UUIDv7 specification.
	 *
	 * This function creates a UUID based on the current Unix timestamp in milliseconds,
	 * ensuring that the UUID conforms to the version 7 specification.
	 * It incorporates both time-based and random components to produce a unique identifier.
	 *
	 * @return string A string representation of a UUIDv7.
	 *
	 * @throws IllegalStateException if the `random_bytes()` function fails.
	 */
	public static function uuidv7(): string {
		$timestamp = (int) (microtime(true) * 1000);

		$timeHex = str_pad(dechex($timestamp), 12, '0', STR_PAD_LEFT);

		$randomBytes = IllegalStateException::try(fn() => random_bytes(10));
		$randomHex = bin2hex($randomBytes);

		$time_low = substr($timeHex, 0, 8); // First 32 bits of timestamp
		$time_mid = substr($timeHex, 8, 4); // Next 16 bits of timestamp

		$time_hi_and_version = substr($randomHex, 0, 4);
		$time_hi_and_version = dechex((hexdec($time_hi_and_version) & 0x0FFF) | 0x7000);

		$clock_seq_hi_and_reserved = substr($randomHex, 4, 2);
		$clock_seq_hi_and_reserved = dechex((hexdec($clock_seq_hi_and_reserved) & 0x3F) | 0x80);

		$clock_seq_low = substr($randomHex, 6, 2);
		$node = substr($randomHex, 8, 12);

		return sprintf('%s-%s-%s-%s%s-%s', $time_low, $time_mid, $time_hi_and_version,
				str_pad($clock_seq_hi_and_reserved, 2, '0', STR_PAD_LEFT),
				$clock_seq_low, $node);
	}

	static function randomToken(int $bytesNum = 16): string {
		return base_convert(bin2hex(IllegalStateException::try(fn () => random_bytes($bytesNum))),16,36);
	}
}