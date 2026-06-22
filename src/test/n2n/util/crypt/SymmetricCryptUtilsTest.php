<?php
namespace n2n\util\crypt;

use PHPUnit\Framework\TestCase;

class SymmetricCryptUtilsTest extends TestCase {
	function testEncryptDecryptRoundtrip(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt(PlainSecret::fromString('very secret data'), str_repeat('s', 32));

		$this->assertSame('very secret data', SymmetricCryptUtils::decrypt($encryptedResult, str_repeat('s', 32))->reveal());
	}

	function testSamePlaintextEncryptedTwiceProducesDifferentPayloads(): void {
		$encryptedResultOne = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32));
		$encryptedResultTwo = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32));

		$this->assertNotSame($encryptedResultOne->toJson(), $encryptedResultTwo->toJson());
	}

	function testJsonRoundtrip(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32));

		$this->assertArrayNotHasKey('algorithm', $encryptedResult->toArray());
		$this->assertSame($encryptedResult->toArray(), EncryptedSecret::fromJson($encryptedResult->toJson())->toArray());
	}

	function testCanUseSupportedAlgorithm(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 16), null,
				SymmetricCipher::AES_128_GCM);

		$this->assertSame('secret-data', SymmetricCryptUtils::decrypt($encryptedResult, str_repeat('s', 16), null,
				SymmetricCipher::AES_128_GCM)->reveal());
	}

	function testDecryptFailsIfWrongAlgorithmIsUsed(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 16), null,
				SymmetricCipher::AES_128_GCM);

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($encryptedResult, str_repeat('s', 16));
	}

	function testDecryptFailsIfCiphertextIsModified(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32));

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($this->tamper($encryptedResult, 'ciphertext'), str_repeat('s', 32));
	}

	function testDecryptFailsIfTagIsModified(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32));

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($this->tamper($encryptedResult, 'tag'), str_repeat('s', 32));
	}

	function testDecryptFailsIfNonceIsModified(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32));

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($this->tamper($encryptedResult, 'nonce'), str_repeat('s', 32));
	}

	function testDecryptFailsIfAadChanges(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32),
				'aad-a');

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($encryptedResult, str_repeat('s', 32), 'aad-b');
	}

	function testNullAadRoundtrip(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt(PlainSecret::fromString('secret-data'), str_repeat('s', 32), null);

		$this->assertSame('secret-data', SymmetricCryptUtils::decrypt($encryptedResult, str_repeat('s', 32), null)
				->reveal());
	}

	function testPlainSecretRepresentationIsRedacted(): void {
		$plainSecret = PlainSecret::fromString('secret-data');

		$this->assertSame('[REDACTED]', (string) $plainSecret);
		$this->assertSame(['value' => '[REDACTED]'], $plainSecret->__debugInfo());
		$this->expectException(PlainSecretSerializationException::class);
		json_encode($plainSecret, JSON_THROW_ON_ERROR);
	}

	private function tamper(EncryptedSecret $encryptedResult, string $field): EncryptedSecret {
		$data = $encryptedResult->toArray();
		$raw = base64_decode($data[$field], true);
		$raw[0] = chr(ord($raw[0]) ^ 1);
		$data[$field] = base64_encode($raw);
		return EncryptedSecret::fromArray($data);
	}
}
