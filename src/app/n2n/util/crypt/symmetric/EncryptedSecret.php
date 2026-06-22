<?php
namespace n2n\util\crypt\symmetric;

use n2n\util\StringUtils;
use n2n\util\type\attrs\AttributesException;
use n2n\util\type\attrs\DataMap;
use n2n\util\type\attrs\InvalidAttributeException;
use n2n\util\type\attrs\MissingAttributeFieldException;

final class EncryptedSecret implements \JsonSerializable {
	function __construct(readonly string $nonce, readonly string $tag, readonly string $ciphertext) {
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 * @throws AttributesException
	 */
	static function fromJson(string $json): self {
		try {
			return self::fromArray(StringUtils::jsonDecode($json, true));
		} catch (\JsonException $e) {
			throw new AttributesException('Invalid encrypted secret: ' . $e->getMessage(), previous: $e);
		}
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	static function fromArray(array $data): self {
		$dataMap = new DataMap($data);
		return new EncryptedSecret($dataMap->reqString('nonce', lenient: false),
				$dataMap->reqString('tag', lenient: false),
				$dataMap->reqString('ciphertext', lenient: false));
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

	function jsonSerialize(): array {
		return $this->toArray();
	}
}
