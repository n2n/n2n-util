<?php
namespace n2n\util\crypt;

use PHPUnit\Framework\TestCase;

class SymmetricCryptUtilsTest extends TestCase {
	function testEncryptDecryptRoundtrip(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt('very secret data', str_repeat('s', 32));

		$this->assertSame('very secret data', SymmetricCryptUtils::decrypt($encryptedResult, str_repeat('s', 32)));
	}

	function testSamePlaintextEncryptedTwiceProducesDifferentPayloads(): void {
		$encryptedResultOne = SymmetricCryptUtils::encrypt('secret-data', str_repeat('s', 32));
		$encryptedResultTwo = SymmetricCryptUtils::encrypt('secret-data', str_repeat('s', 32));

		$this->assertNotSame($encryptedResultOne->toJson(), $encryptedResultTwo->toJson());
	}

	function testJsonContainsAlgorithm(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt('secret-data', str_repeat('s', 32));

		$this->assertSame('aes-256-gcm', $encryptedResult->getAlgorithm());
		$this->assertSame($encryptedResult->toArray(), EncryptedResult::fromJson($encryptedResult->toJson())->toArray());
	}

	function testDecryptRejectsUnsupportedStoredAlgorithm(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt('secret-data', str_repeat('s', 32));
		$data = $encryptedResult->toArray();
		$data['algorithm'] = 'aes-128-gcm';

		$this->expectException(\InvalidArgumentException::class);
		SymmetricCryptUtils::decrypt(EncryptedResult::fromArray($data), str_repeat('s', 32));
	}

	function testDecryptFailsIfCiphertextIsModified(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt('secret-data', str_repeat('s', 32));

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($this->tamper($encryptedResult, 'ciphertext'), str_repeat('s', 32));
	}

	function testDecryptFailsIfTagIsModified(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt('secret-data', str_repeat('s', 32));

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($this->tamper($encryptedResult, 'tag'), str_repeat('s', 32));
	}

	function testDecryptFailsIfNonceIsModified(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt('secret-data', str_repeat('s', 32));

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($this->tamper($encryptedResult, 'nonce'), str_repeat('s', 32));
	}

	function testDecryptFailsIfAadChanges(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt('secret-data', str_repeat('s', 32), 'aad-a');

		$this->expectException(DecryptionFailedException::class);
		SymmetricCryptUtils::decrypt($encryptedResult, str_repeat('s', 32), 'aad-b');
	}

	function testNullAadRoundtrip(): void {
		$encryptedResult = SymmetricCryptUtils::encrypt('secret-data', str_repeat('s', 32), null);

		$this->assertSame('secret-data', SymmetricCryptUtils::decrypt($encryptedResult, str_repeat('s', 32), null));
	}

	private function tamper(EncryptedResult $encryptedResult, string $field): EncryptedResult {
		$data = $encryptedResult->toArray();
		$raw = base64_decode($data[$field], true);
		$raw[0] = chr(ord($raw[0]) ^ 1);
		$data[$field] = base64_encode($raw);
		return EncryptedResult::fromArray($data);
	}
}
