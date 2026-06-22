<?php
namespace n2n\util\crypt\symmetric;


use n2n\util\crypt\OpenSslUtils;
use n2n\util\crypt\PlainSecret;

class SymmetricCryptUtils {
	static function encrypt(PlainSecret $plainSecret, string $key, ?string $aad = null,
			SymmetricCipher $algorithm = SymmetricCipher::AES_256_GCM): EncryptedSecret {
		$nonce = OpenSslUtils::randomPseudoBytes(12);
		$tag = '';
		$ciphertext = OpenSslUtils::encrypt($plainSecret->reveal(), $algorithm->value, $key, OPENSSL_RAW_DATA, $nonce,
				$tag, $aad ?? '');
		return new EncryptedSecret(base64_encode($nonce), base64_encode($tag), base64_encode($ciphertext));
	}

	static function decrypt(EncryptedSecret $encryptedSecret, string $key, ?string $aad = null,
			SymmetricCipher $algorithm = SymmetricCipher::AES_256_GCM): PlainSecret {
		return PlainSecret::fromString(OpenSslUtils::decrypt(self::base64Decode($encryptedSecret->getCiphertext()),
				$algorithm->value, $key, OPENSSL_RAW_DATA, self::base64Decode($encryptedSecret->getNonce()),
				self::base64Decode($encryptedSecret->getTag()), $aad ?? ''));
	}

	private static function base64Decode(string $value): string {
		$decoded = base64_decode($value, true);
		if ($decoded === false) {
			throw new \InvalidArgumentException('Invalid encrypted secret.');
		}
		return $decoded;
	}
}
