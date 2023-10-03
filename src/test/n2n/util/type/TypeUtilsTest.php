<?php

namespace n2n\util\type;

use PHPUnit\Framework\TestCase;

class TypeUtilsTest extends TestCase {

	function testIsValueA() {
		$this->assertTrue(TypeUtils::isValueA(new \ArrayObject(), 'Countable|ArrayAccess', false));
		$this->assertTrue(TypeUtils::isValueA(null, ['string', 'null']));
		$this->assertTrue(TypeUtils::isValueA(null, ['string', null]));
	}
}