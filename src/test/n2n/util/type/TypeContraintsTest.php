<?php
namespace n2n\util\type;

use PHPUnit\Framework\TestCase;
use n2n\util\type\mock\TypedMethodsMock;

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
	
	function testTypeInt() {
		$class = new \ReflectionClass(TypedMethodsMock::class);
		$parameter = $class->getMethod('intParam')->getParameters()[0];
		
		$typeConstraint = TypeConstraints::type($parameter);
		$this->assertInstanceOf(TypeConstraint::class, $typeConstraint);
		$this->assertEquals(TypeName::INT, $typeConstraint->getTypeName());
		$this->assertEquals(false, $typeConstraint->allowsNull());
		
		$typeConstraint = TypeConstraints::type($parameter->getType());
		$this->assertInstanceOf(TypeConstraint::class, $typeConstraint);
		$this->assertEquals(TypeName::INT, $typeConstraint->getTypeName());
		$this->assertEquals(false, $typeConstraint->allowsNull());
	}
	
	function testTypeIntString() {
		$class = new \ReflectionClass(TypedMethodsMock::class);
		$parameter = $class->getMethod('intStringParam')->getParameters()[0];
		$types = $parameter->getType()->getTypes();		
		
		$unionTypeConstraint = TypeConstraints::type($parameter);
		$this->assertInstanceOf(UnionTypeConstraint::class, $unionTypeConstraint);
		
		$typeConstraints = $unionTypeConstraint->getTypeConstraints();
		$this->assertEquals(2, count($typeConstraints));
		$this->assertEquals(TypeName::STRING, $typeConstraints[0]->getTypeName());
		$this->assertEquals(false, $typeConstraints[0]->allowsNull());
		$this->assertEquals(TypeName::INT, $typeConstraints[1]->getTypeName());
		$this->assertEquals(false, $typeConstraints[1]->allowsNull());
		
		$unionTypeConstraint = TypeConstraints::type($parameter->getType());
		$this->assertInstanceOf(UnionTypeConstraint::class, $unionTypeConstraint);
		
		$typeConstraints = $unionTypeConstraint->getTypeConstraints();
		$this->assertEquals(2, count($typeConstraints));
		$this->assertEquals(TypeName::STRING, $typeConstraints[0]->getTypeName());
		$this->assertEquals(false, $typeConstraints[0]->allowsNull());
		$this->assertEquals(TypeName::INT, $typeConstraints[1]->getTypeName());
		$this->assertEquals(false, $typeConstraints[1]->allowsNull());
		
		$unionTypeConstraint = TypeConstraints::type('int|string');
		$this->assertInstanceOf(UnionTypeConstraint::class, $unionTypeConstraint);
		
		$typeConstraints = $unionTypeConstraint->getTypeConstraints();
		$this->assertEquals(2, count($typeConstraints));
		$this->assertEquals(TypeName::INT, $typeConstraints[0]->getTypeName());
		$this->assertEquals(false, $typeConstraints[0]->allowsNull());
		$this->assertEquals(TypeName::STRING, $typeConstraints[1]->getTypeName());
		$this->assertEquals(false, $typeConstraints[1]->allowsNull());
	}
}