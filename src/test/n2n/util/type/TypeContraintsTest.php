<?php
namespace n2n\util\type;

use PHPUnit\Framework\TestCase;

class TypeConstraintsTest extends TestCase {
	
	function testInt() {
		$typeConstraint = TypeConstraints::int();
		
		$this->assertTrue($typeConstraint->isValueValid(2));
		$this->assertFalse($typeConstraint->isValueValid('2'));
		
		$typeConstraint->validate(2);
		
		$this->expectException(ValueIncompatibleWithConstraintsException::class);
		$typeConstraint->validate('2');
	}
	
	function testConvertableInt() {
		$typeConstraint = TypeConstraints::int(false, true);
		
		$this->assertTrue($typeConstraint->isValueValid(2));
		$this->assertTrue($typeConstraint->isValueValid('2'));
		$this->assertFalse($typeConstraint->isValueValid('zwei'));
		
		
		$this->assertEquals(2, $typeConstraint->validate(2));
		$this->assertEquals(2, $typeConstraint->validate('2'));
		
		$this->expectException(ValueIncompatibleWithConstraintsException::class);
		$typeConstraint->validate('zwei');
	}
}