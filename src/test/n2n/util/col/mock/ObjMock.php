<?php

namespace n2n\util\col\mock;

class ObjMock implements \Stringable {

	function __construct(public readonly string $value) {

	}

	function __toString(): string {
		return $this->value;
	}
}