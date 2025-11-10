<?php

namespace n2n\util\col;

use PHPUnit\Framework\TestCase;
use n2n\util\col\mock\ObjMockArray;
use n2n\util\type\NamedTypeConstraint;
use n2n\util\col\mock\ObjMock;
use n2n\util\col\mock\SubObjMockArray;
use n2n\util\col\mock\SubObjMock;
use n2n\util\col\mock\InvalidSubObjMockArray;
use n2n\util\ex\err\ConfigurationError;
use n2n\util\col\attribute\ValueType;
use n2n\util\col\mock\ObjMockKeyArray;
use n2n\util\col\mock\SubObjMockKeyArray;
use n2n\util\col\mock\InvalidSubObjMockKeyArray;
use n2n\util\col\attribute\KeyType;
use n2n\util\col\mock\InheritObjMockKeyArray;

class CollectionTypeUtilsTest extends TestCase {

	function testDetectValueTypeConstraint(): void {
		$this->assertEquals(
				NamedTypeConstraint::createSimple(ObjMock::class, false, false),
				CollectionTypeUtils::detectValueTypeConstraint(new \ReflectionClass(ObjMockArray::class)));
		$this->assertEquals(
				NamedTypeConstraint::createSimple(SubObjMock::class, false, false),
				CollectionTypeUtils::detectValueTypeConstraint(new \ReflectionClass(SubObjMockArray::class)));
	}

	function testDetectValueTypeConstraintFail(): void {
		$this->expectException(ConfigurationError::class);
		$this->expectExceptionMessage(ValueType::class . ' attribute of ' . InvalidSubObjMockArray::class
				. ' conflicts with the ' . ValueType::class . ' attribute of ' . SubObjMockArray::class);

		CollectionTypeUtils::detectValueTypeConstraint(new \ReflectionClass(InvalidSubObjMockArray::class));
	}

	function testDetectKeyTypeConstraint(): void {
		$this->assertEquals(
				NamedTypeConstraint::createSimple(ObjMock::class, false, false),
				CollectionTypeUtils::detectKeyTypeConstraint(new \ReflectionClass(ObjMockKeyArray::class)));
		$this->assertEquals(
				NamedTypeConstraint::createSimple(SubObjMock::class, false, false),
				CollectionTypeUtils::detectKeyTypeConstraint(new \ReflectionClass(SubObjMockKeyArray::class)));
		$this->assertEquals(
				NamedTypeConstraint::createSimple(ObjMock::class, false, false),
				CollectionTypeUtils::detectKeyTypeConstraint(new \ReflectionClass(InheritObjMockKeyArray::class)));
	}

	function testDetectKeyTypeConstraintFail(): void {
		$this->expectException(ConfigurationError::class);
		$this->expectExceptionMessage(KeyType::class . ' attribute of ' . InvalidSubObjMockKeyArray::class
				. ' conflicts with the ' . KeyType::class . ' attribute of ' . ObjMockKeyArray::class);

		CollectionTypeUtils::detectKeyTypeConstraint(new \ReflectionClass(InvalidSubObjMockKeyArray::class));
	}
}