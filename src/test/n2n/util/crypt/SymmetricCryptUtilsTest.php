<?php
namespace n2n\util\crypt;

use PHPUnit\Framework\TestCase;

class SymmetricCryptUtilsTest extends TestCase {
	function testEncryptDecryptRoundtrip(): void {
		$encryptedSecret = SymmetricCryptUtils::encrypt(PlainSecret::fromString('very secret data'), str_repeat('s', 32));

		$this->assertSame('very secret data', SymmetricCryptUtils::decrypt($encryptedSecret, str_repeat('s', 32))->reveal());
	}

	function testSamePlaintextEncryptedTwiceProducesDifferentPayloads(): void {
		$encryptedSecretOne = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32));
		$encryptedSecretTwo = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32));

		$this->assertNotSame($encryptedSecretOne->toJson(), $encryptedSecretTwo->toJson());
	}

	function testJsonRoundtrip(): void {
		$encryptedSecret = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32));

		$this->assertArrayNotHasKey('algorithm', $encryptedSecret->toArray());
		$this->assertSame($encryptedSecret->toArray(), EncryptedSecret::fromJson($encryptedSecret->toJson())->toArray());
	}

	function testEncryptedSecretRejectsInvalidPayload(): void {
		$this->expectException(\InvalidArgumentException::class);

		EncryptedSecret::fromArray(['nonce' => 'nonce', 'tag' => 'tag', 'ciphertext' => 123]);
	}

	function testCanUseSupportedAlgorithm(): void {
		$encryptedSecret = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 16), null,
				SymmetricCipher::AES_128_GCM);

		$this->assertSame('secret-data', SymmetricCryptUtils::decrypt($encryptedSecret, str_repeat('s', 16), null,
				SymmetricCipher::AES_128_GCM)->reveal());
	}

	function testDecryptFailsIfWrongAlgorithmIsUsed(): void {
		$encryptedSecret = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 16), null,
				SymmetricCipher::AES_128_GCM);

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($encryptedSecret, str_repeat('s', 16));
	}

	function testDecryptFailsIfCiphertextIsModified(): void {
		$encryptedSecret = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32));

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($this->tamper($encryptedSecret, 'ciphertext'), str_repeat('s', 32));
	}

	function testDecryptFailsIfTagIsModified(): void {
		$encryptedSecret = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32));

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($this->tamper($encryptedSecret, 'tag'), str_repeat('s', 32));
	}

	function testDecryptFailsIfNonceIsModified(): void {
		$encryptedSecret = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32));

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($this->tamper($encryptedSecret, 'nonce'), str_repeat('s', 32));
	}

	function testDecryptFailsIfAadChanges(): void {
		$encryptedSecret = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32),
				'aad-a');

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($encryptedSecret, str_repeat('s', 32), 'aad-b');
	}

	function testNullAadRoundtrip(): void {
		$encryptedSecret = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32), null);

		$this->assertSame('secret-data', SymmetricCryptUtils::decrypt($encryptedSecret, str_repeat('s', 32), null)
				->reveal());
	}

	function testPlainSecretRepresentationIsRedacted(): void {
		$plainSecret = PlainSecret::fromString('secret-data');

		$this->assertSame('[REDACTED]', (string) $plainSecret);
		$this->assertSame(['value' => '[REDACTED]'], $plainSecret->__debugInfo());
		$this->expectException(PlainSecretSerializationException::class);
		json_encode($plainSecret, JSON_THROW_ON_ERROR);
	}

	private function tamper(EncryptedSecret $encryptedSecret, string $field): EncryptedSecret {
		$data = $encryptedSecret->toArray();
		$raw = base64_decode($data[$field], true);
		$raw[0] = chr(ord($raw[0]) ^ 1);
		$data[$field] = base64_encode($raw);
		return EncryptedSecret::fromArray($data);
	}
}
