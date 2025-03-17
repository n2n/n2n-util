<?php

namespace n2n\util\test;

class TestObjWithToString {
	private array $fullname;

	public function __construct(string $firstname, string $lastname) {
		$this->fullname = ['first' => $firstname, 'last' => $lastname];
	}

	public function __toString(): string {
		return $this->fullname['first'] . ' ' . $this->fullname['last'];
	}
}