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
}