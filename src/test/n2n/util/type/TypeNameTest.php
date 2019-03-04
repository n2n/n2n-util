<?php
namespace n2n\util\type;

use PHPUnit\Framework\TestCase;

class TypeNameTest extends TestCase {
	
	function testIsA() {
		$this->assertTrue(TypeName::isA('string', 'mixed'));
	}
	
	function testA() {
		$this->assertTrue(TypeName::isValueA('somestring', 'mixed'));
		
		$this->assertTrue(TypeName::isValueA(true, 'scalar'));
	}
}