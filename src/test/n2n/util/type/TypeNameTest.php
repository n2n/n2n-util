<?php
namespace n2n\util\type;

use PHPUnit\Framework\TestCase;
use n2n\util\type\mock\TypedMethodsMock;

class TypeNameTest extends TestCase {
	
	function testIsA() {
		$this->assertTrue(TypeName::isA('string', 'mixed'));
	}
	
	function testA() {
		$this->assertTrue(TypeName::isValueA('somestring', 'mixed'));
		
		$this->assertTrue(TypeName::isValueA(true, 'scalar'));
	}
	
	function testIsConvertable() {
		$this->assertTrue('1' === TypeName::convertValue(1, 'string'));
		$this->assertTrue('-1' === TypeName::convertValue(-1, 'string'));
		$this->assertTrue('1.1' === TypeName::convertValue(1.1, 'string'));
		$this->assertTrue('1' === TypeName::convertValue(true, 'string'));
		$this->assertTrue('' === TypeName::convertValue(false, 'string'));
		try {
			TypeName::convertValue(null, 'string');
			$this->fail('no ex thrown');
		} catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
			
		$this->assertTrue(1 === TypeName::convertValue('1', 'int'));
		$this->assertTrue(-1 === TypeName::convertValue('-1', 'int'));
		$this->assertTrue(1 === TypeName::convertValue(1, 'int'));
		try {
			TypeName::convertValue(null, 'string');
			$this->fail('no ex thrown');
		} catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
		try {
			$this->assertTrue('1.1' === TypeName::convertValue(1.1, 'int'));
		} catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
	}

	function testObject(): void {
		$this->assertTrue(TypeName::isA(TypedMethodsMock::class, 'object'));
		$this->assertTrue(TypeName::isA(\ArrayObject::class, 'object'));
		$this->assertTrue(TypeName::isA('object', 'object'));
		$this->assertFalse(TypeName::isA('string', 'object'));
		$this->assertFalse(TypeName::isA('does\not\Exist', 'object'));
	}

	function testIsIntersectionType() {
		$this->assertTrue(TypeName::isIntersectionType('huii&hoi'));
		$this->assertTrue(TypeName::isIntersectionType('huii & hoi '));
		$this->assertTrue(TypeName::isIntersectionType('(huii&hoi)'));
		$this->assertFalse(TypeName::isIntersectionType('holeardio|(huii&hoi)'));
	}

	function testExtractIntersectionTypes() {
		$this->assertEquals(['huii', 'hoi'], TypeName::extractIntersectionTypeNames('huii&hoi'));
		$this->assertEquals(['huii', 'hoi'], TypeName::extractIntersectionTypeNames('huii & hoi '));
		$this->assertEquals(['huii', 'hoi'], TypeName::extractIntersectionTypeNames(' (huii&hoi)'));
	}
}