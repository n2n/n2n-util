<?php

namespace n2n\util\test;

class TestObjWithToString {
	private string $value;

	public function __construct(string $value) {
		$this->value = $value;
	}

	public function __toString(): string {
		return $this->value;
	}
}