<?php
namespace n2n\util\type;

use PHPUnit\Framework\TestCase;
use n2n\util\type\mock\TypedMethodsMock;
use n2n\util\uri\Url;
use n2n\util\type\mock\StringBackedEnumMock;
use n2n\util\type\mock\PureEnumMock;

class TypeConstraintsTest extends TestCase {
	
	function testInt() {
		$typeConstraint = TypeConstraints::int();
		$this->assertInstanceOf(NamedTypeConstraint::class, $typeConstraint);
		$this->assertFalse($typeConstraint->allowsNull());
		$this->assertFalse($typeConstraint->isConvertable());
		
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
	
	function testTypeIntString() {
		$typeConstraint = TypeConstraints::type('int|string');
		$this->assertInstanceOf(UnionTypeConstraint::class, $typeConstraint);
		
		$this->assertTrue($typeConstraint->isValueValid(2));
		$this->assertTrue($typeConstraint->isValueValid('2'));
		$this->assertTrue($typeConstraint->isValueValid('zwei'));
		
		$this->assertTrue(2  === $typeConstraint->validate(2));
		$this->assertTrue('2'  === $typeConstraint->validate('2'));
		$this->assertTrue('zwei' ===  $typeConstraint->validate('zwei'));
		
		$this->expectException(ValueIncompatibleWithConstraintsException::class);
		$typeConstraint->validate(null);
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
	
	function testTypeIntStringByParameter() {
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
	
	
	function testTypeStringIntByParameter() {
		$class = new \ReflectionClass(TypedMethodsMock::class);
		$parameter = $class->getMethod('stringIntParam')->getParameters()[0];
		$types = $parameter->getType()->getTypes();
		
		$unionTypeConstraint = TypeConstraints::type($parameter);
		$this->assertInstanceOf(UnionTypeConstraint::class, $unionTypeConstraint);
		
		$typeConstraints = $unionTypeConstraint->getTypeConstraints();
		$this->assertEquals(2, count($typeConstraints));
		$this->assertEquals(TypeName::STRING, $typeConstraints[0]->getTypeName());
		$this->assertEquals(false, $typeConstraints[0]->allowsNull());
		$this->assertEquals(TypeName::INT, $typeConstraints[1]->getTypeName());
		$this->assertEquals(false, $typeConstraints[1]->allowsNull());
	}
	
	function testTypeConvertable() {
		$namedTypeConstraint = TypeConstraints::type('?int', true);
		$this->assertInstanceOf(NamedTypeConstraint::class, $namedTypeConstraint);
		
		$this->assertEquals(TypeName::INT, $namedTypeConstraint->getTypeName());
		$this->assertEquals(true, $namedTypeConstraint->allowsNull());
		$this->assertEquals(true, $namedTypeConstraint->isConvertable());
		
		$unionTypeConstraint = TypeConstraints::type('string|int|null', true);
		$this->assertInstanceOf(UnionTypeConstraint::class, $unionTypeConstraint);
		
		$typeConstraints = $unionTypeConstraint->getTypeConstraints();
		$this->assertEquals(3, count($typeConstraints));
		
		$this->assertEquals(TypeName::STRING, $typeConstraints[0]->getTypeName());
		$this->assertEquals(false, $typeConstraints[0]->allowsNull());
		$this->assertEquals(true, $typeConstraints[0]->isConvertable());
		
		$this->assertEquals(TypeName::INT, $typeConstraints[1]->getTypeName());
		$this->assertEquals(false, $typeConstraints[1]->allowsNull());
		$this->assertEquals(true, $typeConstraints[1]->isConvertable());
		
		$this->assertEquals(TypeName::NULL, $typeConstraints[2]->getTypeName());
		$this->assertEquals(true, $typeConstraints[2]->allowsNull());
		$this->assertEquals(false, $typeConstraints[2]->isConvertable());
		
		
		$this->assertTrue('2' === $unionTypeConstraint->validate('2'));
		$this->assertTrue('2' === $unionTypeConstraint->validate(2));
	}

	function testUnion() {
		$typeConstraint = TypeConstraints::type('string|null');
		$this->assertEquals('huii', $typeConstraint->validate('huii'));
		$this->assertEquals(null, $typeConstraint->validate(null));

		try {
			$typeConstraint->validate(array());
			$this->fail();
		} catch (ValueIncompatibleWithConstraintsException $e) {
		}
	}

	function testUnionWithArray() {
		$typeConstraint = TypeConstraints::type(['string', 'null', Url::class]);
		$this->assertEquals('huii', $typeConstraint->validate('huii'));
		$this->assertEquals(null, $typeConstraint->validate(null));
		$url = new Url();
		$this->assertEquals($url, $typeConstraint->validate($url));

		try {
			$typeConstraint->validate(array());
			$this->fail();
		} catch (ValueIncompatibleWithConstraintsException $e) {
		}
	}

	function testEnum() {
		$typeConstraint = TypeConstraints::type(StringBackedEnumMock::class);

		$this->assertTrue($typeConstraint->isValueValid(StringBackedEnumMock::VALUE1));
		$this->assertFalse($typeConstraint->isValueValid(PureEnumMock::CASE2));
	}
}