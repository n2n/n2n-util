<?php
namespace n2n\util\crypt;

class SymmetricCryptUtils {
	private const ALGORITHM = 'aes-256-gcm';

	static function encrypt(string $data, string $key, ?string $aad = null): EncryptedResult {
		$nonce = OpenSslUtils::randomPseudoBytes(12);
		$tag = '';
		$ciphertext = OpenSslUtils::encrypt($data, self::ALGORITHM, $key, OPENSSL_RAW_DATA, $nonce, $tag, $aad ?? '');
		return new EncryptedResult(self::ALGORITHM, base64_encode($nonce), base64_encode($tag),
				base64_encode($ciphertext));
	}

	static function decrypt(EncryptedResult $encryptedResult, string $key, ?string $aad = null): string {
		if ($encryptedResult->getAlgorithm() !== self::ALGORITHM) {
			throw new \InvalidArgumentException('Unsupported symmetric encryption algorithm: '
					. $encryptedResult->getAlgorithm() . '. Supported: ' . self::ALGORITHM);
		}

		return OpenSslUtils::decrypt(self::decode($encryptedResult->getCiphertext()), $encryptedResult->getAlgorithm(),
				$key, OPENSSL_RAW_DATA, self::decode($encryptedResult->getNonce()),
				self::decode($encryptedResult->getTag()), $aad ?? '');
	}

	private static function decode(string $value): string {
		$decoded = base64_decode($value, true);
		if ($decoded === false) {
			throw new \InvalidArgumentException('Invalid encrypted result.');
		}

		return $decoded;
	}
}
