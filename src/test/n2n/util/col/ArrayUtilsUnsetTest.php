<?php

namespace n2n\util\col;

use PHPUnit\Framework\TestCase;
use ArrayObject;

class ArrayUtilsUnsetTest extends TestCase {

	protected function setUp(): void {
	}

	//delete value from original Array/ArrayLike
	function testUnsetByValueWithNumArray() {
		$numArray = [2, 5, 3, 1, 4];
		$numArrayCopy = $numArray;
		$unsetByValue = ArrayUtils::unsetByValue($numArray, 3);
		$this->assertTrue($unsetByValue);
		$this->assertNotContainsEquals(3, $numArray);
		$this->assertNotEquals($numArray, $numArrayCopy);
	}

	function testUnsetByValueStrict() {
		$numArray = [2, 5, 3, 1, 4];
		$this->assertFalse(ArrayUtils::unsetByValue($numArray, '3', true));
		$this->assertTrue(ArrayUtils::unsetByValue($numArray, '3', false));
	}

	function testUnsetByValueWithAlphaArray() {
		$alphaArray = ['b', 'e', 'c', 'a', 'd'];
		$alphaArrayCopy = $alphaArray;
		$unsetByValue = ArrayUtils::unsetByValue($alphaArray, 'c');
		$this->assertTrue($unsetByValue);
		$this->assertNotContainsEquals('c', $alphaArray);
		$this->assertNotEquals($alphaArray, $alphaArrayCopy);
	}

	function testUnsetByValueWithObjectArrayStrictExpectFalse() {
		//when using strict compared object are not equals when they have same values but are not same instance
		$objectArray = [new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']), new ArrayObject(['a']), new ArrayObject(['d'])];
		$unsetByValue = ArrayUtils::unsetByValue($objectArray, new ArrayObject(['c']));
		$this->assertFalse($unsetByValue);
	}

	function testUnsetByValueWithObjectArray() {
		//when using strict compared object are equals when they are created by same instance
		$objectArray = [new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']), new ArrayObject(['a']), new ArrayObject(['d'])];
		$objectArrayCopy = [new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']), new ArrayObject(['a']), new ArrayObject(['d'])];
		$newObjectArray = $objectArray;
		$f = new ArrayObject(['f']);
		$newObjectArray[] = $f;
		$unsetByValue = ArrayUtils::unsetByValue($newObjectArray, $f);
		$this->assertTrue($unsetByValue);
		$this->assertNotContainsEquals($f, $newObjectArray);

		//when not using strict compared object are equals when they have same values
		$unsetByValue = ArrayUtils::unsetByValue($objectArray, new ArrayObject(['c']), false);
		$this->assertTrue($unsetByValue);
		$this->assertNotContainsEquals(new ArrayObject(['c']), $objectArray);
		$this->assertNotEquals($objectArray, $objectArrayCopy);
	}

	function testUnsetByValueWithNumArrayObject() {
		$numArrayObject = new ArrayObject([2, 5, 3, 1, 4]);
		$numArrayObjectCopy = new ArrayObject([2, 5, 3, 1, 4]);
		$unsetByValue = ArrayUtils::unsetByValue($numArrayObject, 3);
		$this->assertTrue($unsetByValue);
		$this->assertNotContainsEquals(3, $numArrayObject);
		$this->assertNotEquals($numArrayObject, $numArrayObjectCopy);
	}

	function testUnsetByValueWithAlphaArrayObject() {
		$alphaArrayObject = new ArrayObject(['b', 'e', 'c', 'a', 'd']);
		$alphaArrayObjectCopy = new ArrayObject(['b', 'e', 'c', 'a', 'd']);
		$this->assertEquals($alphaArrayObject, $alphaArrayObjectCopy);
		$unsetByValue = ArrayUtils::unsetByValue($alphaArrayObject, 'c');
		$this->assertTrue($unsetByValue);
		$this->assertNotContainsEquals('c', $alphaArrayObject);
		$this->assertNotEquals($alphaArrayObject, $alphaArrayObjectCopy);
	}

	function testUnsetByValueWithObjectArrayObjectStrictExpectFalse() {
		//when using strict compared object are not equals when they have same values but are not same instance
		$objectArrayObject = new ArrayObject([new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']), new ArrayObject(['a']), new ArrayObject(['d'])]);
		$unsetByValue = ArrayUtils::unsetByValue($objectArrayObject, new ArrayObject(['c']));
		$this->assertFalse($unsetByValue);

	}

	function testUnsetByValueWithObjectArrayObject() {
		//when using strict compared object are equals when they are created by same instance
		$objectArrayObject = new ArrayObject([new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']), new ArrayObject(['a']), new ArrayObject(['d'])]);
		$objectArrayObjectCopy = new ArrayObject([new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']), new ArrayObject(['a']), new ArrayObject(['d'])]);
		$newObjectArray = $objectArrayObject;
		$f = new ArrayObject(['f']);
		$newObjectArray->append($f);
		$unsetByValue = ArrayUtils::unsetByValue($newObjectArray, $f);
		$this->assertTrue($unsetByValue);
		$this->assertNotContainsEquals($f, $newObjectArray);

		//when not using strict compared object are equals when they have same values
		$unsetByValue = ArrayUtils::unsetByValue($objectArrayObject, new ArrayObject(['c']), false);
		$this->assertTrue($unsetByValue);
		$this->assertNotContainsEquals(new ArrayObject(['c']), $objectArrayObject);
		$this->assertNotEquals($objectArrayObject, $objectArrayObjectCopy);

	}

	function testUnsetByValueWithEmptyArray() {
		$emptyArray = [];
		$unsetByValue = ArrayUtils::unsetByValue($emptyArray, 1);
		$this->assertFalse($unsetByValue);
	}

	function testUnsetByValueWithEmptyArrayObject() {
		$emptyArrayObject = new ArrayObject();
		$unsetByValue = ArrayUtils::unsetByValue($emptyArrayObject, 1);
		$this->assertFalse($unsetByValue);
	}

	function testUnsetByValueWithSingleValueArray() {
		$singleValueArray = [1];
		$unsetByValue = ArrayUtils::unsetByValue($singleValueArray, 1);
		$this->assertTrue($unsetByValue);
		$this->assertEquals([], $singleValueArray);
		$this->assertCount(0, $singleValueArray);
	}

	function testUnsetByValueWithSingleValueArrayObject() {
		$singleValueArrayObject = new ArrayObject([1]);
		$unsetByValue = ArrayUtils::unsetByValue($singleValueArrayObject, 1);
		$this->assertTrue($unsetByValue);
		$this->assertEquals(new ArrayObject(), $singleValueArrayObject);
		$this->assertCount(0, $singleValueArrayObject);
	}

}