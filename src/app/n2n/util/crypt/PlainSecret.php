<?php
namespace n2n\util\crypt;

class PlainSecret implements \JsonSerializable {
	private string $value;

	function __construct(string $value) {
		$this->value = $value;
	}

	static function from(string|self|null $value): self|null {
		if ($value === null || $value instanceof self) {
			return $value;
		}

		return new self($value);
	}

	function reveal(): string {
		return $this->value;
	}

	function __toString(): string {
		return '[REDACTED]';
	}

	function __debugInfo(): array {
		return ['value' => '[REDACTED]'];
	}

	function jsonSerialize(): string {
		return $this->__toString();
	}
}
