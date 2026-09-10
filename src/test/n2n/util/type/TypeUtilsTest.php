<?php

namespace n2n\util\type;

use PHPUnit\Framework\TestCase;

class TypeUtilsTest extends TestCase {

	function testScalarTypeCompatibility() {
		foreach (['int', 'float', 'bool', 'string'] as $type) {
			$this->assertTrue(TypeUtils::isTypeA($type, 'scalar'), $type);
			$this->assertTrue(TypeUtils::isTypeA($type, $type), $type);
			$this->assertFalse(TypeUtils::isTypeA('scalar', $type), $type);
		}
		$this->assertFalse(TypeUtils::isTypeA('array', 'scalar'));
		$this->assertFalse(TypeUtils::isTypeA(\stdClass::class, 'scalar'));
		$this->assertFalse(TypeUtils::isTypeA('int', 'string'));
		$this->assertFalse(TypeUtils::isTypeA('float', 'int'));
		$this->assertFalse(TypeUtils::isTypeA('bool', 'int'));
	}

	/**
	 * @throws ValueIncompatibleWithConstraintsException
	 */
	function testNullableIntConversionThroughScalarCompatibility() {
		$constraint = TypeConstraints::int(true);
		if ($constraint->isPassableTo(TypeConstraints::scalar(true), true) && !$constraint->isEmpty()) {
			$constraint->setConvertable(true);
		}

		$this->assertSame(2, $constraint->validate('2'));
		$this->assertSame(2, $constraint->validate(2));
		$this->assertNull($constraint->validate(null));
		$this->expectException(ValueIncompatibleWithConstraintsException::class);
		$constraint->validate('invalid');
	}

	function testIsValueA() {
		$this->assertTrue(TypeUtils::isValueA(new \ArrayObject(), 'Countable|ArrayAccess', false));
		$this->assertTrue(TypeUtils::isValueA(null, ['string', 'null']));
		$this->assertTrue(TypeUtils::isValueA(null, ['string', null]));
	}
}