<?php
namespace n2n\util\crypt;

use n2n\util\StringUtils;

final class EncryptedResult implements \JsonSerializable {
	private const FIELDS = ['algorithm', 'nonce', 'tag', 'ciphertext'];

	function __construct(private string $algorithm, private string $nonce, private string $tag,
			private string $ciphertext) {
	}

	static function fromJson(string $json): self {
		try {
			$data = StringUtils::jsonDecode($json, true);
		} catch (\JsonException $e) {
			throw new \InvalidArgumentException('Invalid encrypted result.', previous: $e);
		}

		if (!is_array($data)) {
			throw new \InvalidArgumentException('Invalid encrypted result.');
		}

		return self::fromArray($data);
	}

	static function fromArray(array $data): self {
		foreach (self::FIELDS as $field) {
			if (!array_key_exists($field, $data) || !is_string($data[$field])) {
				throw new \InvalidArgumentException('Invalid encrypted result.');
			}
		}

		return new EncryptedResult($data['algorithm'], $data['nonce'], $data['tag'], $data['ciphertext']);
	}

	function toJson(): string {
		try {
			return StringUtils::jsonEncode($this->toArray(), JSON_UNESCAPED_SLASHES);
		} catch (\JsonException $e) {
			throw new \InvalidArgumentException('Invalid encrypted result.', previous: $e);
		}
	}

	function toArray(): array {
		return [
			'algorithm' => $this->algorithm,
			'nonce' => $this->nonce,
			'tag' => $this->tag,
			'ciphertext' => $this->ciphertext
		];
	}

	function getAlgorithm(): string {
		return $this->algorithm;
	}

	function getNonce(): string {
		return $this->nonce;
	}

	function getTag(): string {
		return $this->tag;
	}

	function getCiphertext(): string {
		return $this->ciphertext;
	}

	function jsonSerialize(): array {
		return $this->toArray();
	}
}
