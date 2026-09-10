<?php

namespace n2n\util\io\fs;

use n2n\util\io\IoUtils;

class FsPerm implements \JsonSerializable, \Stringable {

	function __construct(private int $value) {

	}

	function toInt(): int {
		return $this->value;
	}


	static function from(FsPerm|string|int $value): FsPerm {
		if ($value instanceof FsPerm) {
			return $value;
		}

		return new FsPerm(IoUtils::normalizePermission($value));
	}

	static function build(FsPerm|string|int|null $value): ?FsPerm {
		if ($value === null) {
			return null;
		}

		return self::from($value);
	}

	public function __toString(): string {
		//int 511 for example would return 0777 because octal is easier to understand
		return decoct($this->value);
	}

	public function jsonSerialize(): mixed {
		return $this->value;
	}
}