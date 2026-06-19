<?php
namespace n2n\util\crypt;

use PHPUnit\Framework\TestCase;

class OpenSslUtilsTest extends TestCase {
	function testSimpleSignatureStillWorks(): void {
		$algorithm = 'aes-256-ctr';
		$key = str_repeat('s', 32);
		$iv = str_repeat('i', 16);

		$ciphertext = OpenSslUtils::encrypt('its so secret', $algorithm, $key, 0, $iv);

		$this->assertSame('its so secret', OpenSslUtils::decrypt($ciphertext, $algorithm, $key, 0, $iv));
	}

	function testGcmSignatureWorks(): void {
		$algorithm = 'aes-256-gcm';
		$key = str_repeat('s', 32);
		$nonce = str_repeat('n', 12);
		$tag = '';

		$ciphertext = OpenSslUtils::encrypt('mucho inkognito', $algorithm, $key, OPENSSL_RAW_DATA, $nonce, $tag);
		$this->assertSame('mucho inkognito', OpenSslUtils::decrypt($ciphertext, $algorithm, $key, OPENSSL_RAW_DATA,
				$nonce, $tag));
	}
}
