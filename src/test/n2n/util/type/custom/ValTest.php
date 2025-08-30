<?php

namespace n2n\util\type\custom;

use PHPUnit\Framework\TestCase;

class ValTest extends TestCase {

	function testUndefined(): void {
		$this->assertTrue(Undefined::is(Val::Undefined));
		$this->assertSame(Undefined::val(), Val::Undefined);
	}

	function testIsNullOrUndefined(): void {
		$this->assertTrue(Val::isNullOrUndefined(null));
		$this->assertTrue(Val::isNullOrUndefined(Val::Undefined));
		$this->assertFalse(Val::isNullOrUndefined('huii'));
	}
}