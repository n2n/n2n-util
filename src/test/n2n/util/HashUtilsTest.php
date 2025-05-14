<?php
namespace n2n\util;

use PHPUnit\Framework\TestCase;

class HashUtilsTest extends TestCase {

	public function testHashPasswordReturnsHash() {
		$rawPassword = 'TestPassword123';
		$hashedPassword = HashUtils::hashPassword($rawPassword);

		$this->assertIsString($hashedPassword);
		$this->assertNotEmpty($hashedPassword);
		$this->assertTrue(HashUtils::verifyPassword($rawPassword, $hashedPassword));
	}

	public function testHashPasswordReturnsNullForNullInput() {
		$this->assertNull(HashUtils::hashPassword(null));
	}

	public function testVerifyPasswordReturnsFalseForIncorrectPassword() {
		$rawPassword = 'CorrectPassword';
		$wrongPassword = 'WrongPassword';
		$hashedPassword = HashUtils::hashPassword($rawPassword);

		$this->assertFalse(HashUtils::verifyPassword($wrongPassword, $hashedPassword));
	}

	public function testBase64HashOrShorterValue(): void {
		//test Values for sha3-256 algo
		$inputShortValue = 'blubb';
		$inputEqualValue = 'variable holding a 44-character length value';
		$inputLongValue = 'this is a very long input value that exceed the length of 44 chars';

		$hashShortValue = HashUtils::base64HashOrShorterValue($inputShortValue, 'sha3-256');
		$hashEqualValue = HashUtils::base64HashOrShorterValue($inputEqualValue, 'sha3-256');
		$hashLongValue = HashUtils::base64HashOrShorterValue($inputLongValue, 'sha3-256');

		//short values will be returned without being touched
		$this->assertTrue(strlen($inputShortValue) === strlen($hashShortValue));
		$this->assertEquals($hashShortValue, $inputShortValue);

		//equal length than hash will be hashed and returned
		$this->assertTrue(strlen($inputEqualValue) === strlen($hashEqualValue));
		$this->assertNotEquals($hashEqualValue, $inputEqualValue);

		//longer values will be hashed and made shorter
		$this->assertTrue(strlen($inputLongValue) > strlen($hashLongValue));
		$this->assertNotEquals($hashLongValue, $inputLongValue);


		//if we change to sha3-512 $inputLongValue is not enough to need a hash value but if we triple it we had expected result
		$hashNotEqualValue = HashUtils::base64HashOrShorterValue($inputEqualValue, 'sha3-512');
		$hashNotToLongValue = HashUtils::base64HashOrShorterValue($inputLongValue, 'sha3-512');
		$hashAlsoToLongValue = HashUtils::base64HashOrShorterValue($inputLongValue . $inputLongValue . $inputLongValue, 'sha3-512');

		//equal and long values are returned without hashing because hash sha3-512 is much longer than sha3-256
		$this->assertTrue(strlen($inputEqualValue) === strlen($hashNotEqualValue));
		$this->assertEquals($hashNotEqualValue, $inputEqualValue);
		$this->assertTrue(strlen($inputLongValue) === strlen($hashNotToLongValue));
		$this->assertEquals($inputLongValue, $hashNotToLongValue);
		//but if we exceed the sha3-512 hash-length value will be hashed
		$this->assertTrue(strlen($inputLongValue . $inputLongValue . $inputLongValue) > strlen($hashAlsoToLongValue));
		$this->assertNotEquals($hashAlsoToLongValue, $inputLongValue . $inputLongValue . $inputLongValue);

	}

	function testBase36Sha256Hash(): void {
		$this->assertEquals(
				base_convert(hash('sha256', 'holeradio'), 16, 36),
				HashUtils::base36Sha256Hash('holeradio'));

		$this->assertEquals(
				mb_substr(base_convert(hash('sha256', 'holeradio'), 16, 36), 0, 7),
				HashUtils::base36Sha256Hash('holeradio', 7));
	}
}