<?php
namespace n2n\util\crypt\symmetric;

use n2n\util\StringUtils;
use n2n\util\type\attrs\AttributesException;
use n2n\util\type\attrs\DataMap;

final class EncryptedSecret implements \JsonSerializable {
	function __construct(private string $nonce, private string $tag, private string $ciphertext) {
	}

	static function fromJson(string $json): self {
		try {
			$data = StringUtils::jsonDecode($json, true);
		} catch (\JsonException $e) {
			throw new \InvalidArgumentException('Invalid encrypted secret.', previous: $e);
		}

		if (!is_array($data)) {
			throw new \InvalidArgumentException('Invalid encrypted secret.');
		}

		return self::fromArray($data);
	}

	static function fromArray(array $data): self {
		$dataMap = new DataMap($data);
		try {
			return new EncryptedSecret($dataMap->reqString('nonce', lenient: false),
					$dataMap->reqString('tag', lenient: false),
					$dataMap->reqString('ciphertext', lenient: false));
		} catch (AttributesException $e) {
			throw new \InvalidArgumentException('Invalid encrypted secret.', previous: $e);
		}
	}

	function toJson(): string {
		try {
			return StringUtils::jsonEncode($this->toArray(), JSON_UNESCAPED_SLASHES);
		} catch (\JsonException $e) {
			throw new \InvalidArgumentException('Invalid encrypted secret.', previous: $e);
		}
	}

	function toArray(): array {
		return [
			'nonce' => $this->nonce,
			'tag' => $this->tag,
			'ciphertext' => $this->ciphertext
		];
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
