<?php
namespace n2n\util\crypt;

class SymmetricCryptUtils {
	static function encrypt(PlainSecret $plainSecret, string $key, ?string $aad = null,
			SymmetricCryptAlgorithm $algorithm = SymmetricCryptAlgorithm::AES_256_GCM): EncryptedResult {
		$nonce = OpenSslUtils::randomPseudoBytes(12);
		$tag = '';
		$ciphertext = OpenSslUtils::encrypt($plainSecret->reveal(), $algorithm->value, $key, OPENSSL_RAW_DATA, $nonce,
				$tag, $aad ?? '');
		return new EncryptedResult($algorithm->value, base64_encode($nonce), base64_encode($tag),
				base64_encode($ciphertext));
	}

	static function decrypt(EncryptedResult $encryptedResult, string $key, ?string $aad = null,
			SymmetricCryptAlgorithm $algorithm = SymmetricCryptAlgorithm::AES_256_GCM): PlainSecret {
		if ($encryptedResult->getAlgorithm() !== $algorithm->value) {
			throw new \InvalidArgumentException('Unsupported symmetric encryption algorithm: '
					. $encryptedResult->getAlgorithm() . '. Supported: ' . $algorithm->value);
		}

		return PlainSecret::fromString(OpenSslUtils::decrypt(self::decode($encryptedResult->getCiphertext()),
				$algorithm->value, $key, OPENSSL_RAW_DATA, self::decode($encryptedResult->getNonce()),
				self::decode($encryptedResult->getTag()), $aad ?? ''));
	}

	private static function decode(string $value): string {
		$decoded = base64_decode($value, true);
		if ($decoded === false) {
			throw new \InvalidArgumentException('Invalid encrypted result.');
		}

		return $decoded;
	}
}
