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

}