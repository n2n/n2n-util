<?php

namespace n2n\util\crypt;

use PHPUnit\Framework\TestCase;

class TokenUtilsTest extends TestCase {

	function testRandomToken(): void {
		$this->assertNotEquals(TokenUtils::randomToken(), TokenUtils::randomToken());

		$this->assertTrue(mb_strlen(TokenUtils::randomToken(17))
				> mb_strlen(TokenUtils::randomToken(16)));

		$this->assertGreaterThan(23, mb_strlen(TokenUtils::randomToken()));
	}


	/**
	 * Test that UUIDv4 matches the correct format.
	 */
	public function testUuid4Format() {
		$uuid = TokenUtils::uuidv4();

		// UUIDv4 format: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
		$this->assertMatchesRegularExpression(
				'/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89abAB][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid,
				'UUIDv4 does not match the expected format.');
	}

	/**
	 * Test that the version character of UUIDv4 is '4'.
	 */
	public function testUuid4Version() {
		$uuid = TokenUtils::uuidv4();
		$versionChar = $uuid[14]; // 15th character (0-based index)
		$this->assertEquals('4', $versionChar, 'UUIDv4 version character is not "4".');
	}

	/**
	 * Test that the variant character of UUIDv4 is one of '8', '9', 'A', or 'B'.
	 */
	public function testUuid4Variant() {
		$uuid = TokenUtils::uuidv4();
		$variantChar = strtolower($uuid[19]); // 20th character (0-based index)

		$this->assertContains($variantChar, ['8', '9', 'a', 'b'], 'UUIDv4 variant character is not valid.');
	}

	/**
	 * Test that UUIDv4 returns a string.
	 */
	public function testUuid4ReturnsString() {
		$uuid = TokenUtils::uuidv4();
		$this->assertIsString($uuid, 'UUIDv4 does not return a string.');
	}

	/**
	 * Test that UUIDv7 matches the correct format.
	 */
	public function testUuidv7Format() {
		$uuid = TokenUtils::uuidv7();
		// UUIDv7 format: xxxxxxxx-xxxx-7xxx-yxxx-xxxxxxxxxxxx
		$this->assertMatchesRegularExpression(
				'/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89abAB][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid,
				'UUIDv7 does not match the expected format.');
	}

	/**
	 * Test that the version character of UUIDv7 is '7'.
	 */
	public function testUuidv7Version() {
		$uuid = TokenUtils::uuidv7();
		$versionChar = $uuid[14]; // 15th character (0-based index)
		$this->assertEquals('7', $versionChar, 'UUIDv7 version character is not "7".'
		);
	}

	/**
	 * Test that the variant character of UUIDv7 is one of '8', '9', 'A', or 'B'.
	 */
	public function testUuidv7Variant() {
		$uuid = TokenUtils::uuidv7();
		$variantChar = strtolower($uuid[19]); // 20th character (0-based index)

		$this->assertContains($variantChar, ['8', '9', 'a', 'b'], 'UUIDv7 variant character is not valid.');
	}

	/**
	 * Test that UUIDv7 returns a string.
	 */
	public function testUuidv7ReturnsString() {
		$uuid = TokenUtils::uuidv7();
		$this->assertIsString($uuid, 'UUIDv7 does not return a string.');
	}

	/**
	 * Test that the timestamp in UUIDv7 corresponds to the current time.
	 */
	public function testUuidv7Timestamp() {
		$before = (int) (microtime(true) * 1000);
		$uuid = TokenUtils::uuidv7();
		$after = (int) (microtime(true) * 1000);

		$cleanUuid = str_replace('-', '', $uuid);
		$timeHex = substr($cleanUuid, 0, 12);
		$timestamp = hexdec($timeHex);

		$this->assertGreaterThanOrEqual($before, $timestamp, 'UUIDv7 timestamp is before the test start time.');

		$this->assertLessThanOrEqual($after, $timestamp, 'UUIDv7 timestamp is after the test end time.');
	}
}