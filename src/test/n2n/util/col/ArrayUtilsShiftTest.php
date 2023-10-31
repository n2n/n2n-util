<?php

namespace n2n\util\col;

use PHPUnit\Framework\TestCase;
use ArrayObject;
use OutOfRangeException;

class ArrayUtilsShiftTest extends TestCase {
	protected function setUp(): void {
	}

	//get First value of Array/ArrayLike, and delete it from original Array/ArrayLike
	function testShiftArrayObject() {
		$arrayObject = new ArrayObject(['first', 'second']);
		$this->assertEquals('first', ArrayUtils::shift($arrayObject));
		$this->assertEquals(1, $arrayObject->count());

		$arrayObject = new ArrayObject([]);
		$this->assertEmpty(ArrayUtils::shift($arrayObject));
	}

	function testShiftWithNumArray() {
		$numArray = [2, 5, 3, 1, 4];
		$numArrayCopy = $numArray;
		$this->assertCount(5, $numArray);
		$shift = ArrayUtils::shift($numArray);
		$this->assertEquals(2, $shift);
		$this->assertNotContainsEquals(2, $numArray);
		$this->assertNotEquals($numArray, $numArrayCopy);
		$this->assertCount(4, $numArray);
	}

	function testShiftWithAlphaArray() {
		$alphaArray = ['b', 'e', 'c', 'a', 'd'];
		$alphaArrayCopy = $alphaArray;
		$this->assertCount(5, $alphaArray);
		$shift = ArrayUtils::shift($alphaArray);
		$this->assertEquals('b', $shift);
		$this->assertNotContainsEquals('b', $alphaArray);
		$this->assertNotEquals($alphaArray, $alphaArrayCopy);
		$this->assertCount(4, $alphaArray);
	}

	function testShiftWithObjectArray() {
		$objectArray = [new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']), new ArrayObject(['a']), new ArrayObject(['d'])];
		$objectArrayCopy = $objectArray;
		$this->assertCount(5, $objectArray);
		$shift = ArrayUtils::shift($objectArray);
		$this->assertEquals(new ArrayObject(['b']), $shift);
		$this->assertNotContainsEquals(new ArrayObject(['b']), $objectArray);
		$this->assertNotEquals($objectArray, $objectArrayCopy);
		$this->assertCount(4, $objectArray);
	}

	function testShiftWithNumArrayObject() {
		$numArrayObject = new ArrayObject([2, 5, 3, 1, 4]);
		$numArrayObjectCopy = new ArrayObject([2, 5, 3, 1, 4]);
		$this->assertCount(5, $numArrayObject);
		$shift = ArrayUtils::shift($numArrayObject);
		$this->assertEquals(2, $shift);
		$this->assertNotContainsEquals(2, $numArrayObject);
		$this->assertNotEquals($numArrayObject, $numArrayObjectCopy);
		$this->assertCount(4, $numArrayObject);
	}

	function testShiftWithAlphaArrayObject() {
		$alphaArrayObject = new ArrayObject(['b', 'e', 'c', 'a', 'd']);
		$alphaArrayObjectCopy = new ArrayObject(['b', 'e', 'c', 'a', 'd']);
		$this->assertCount(5, $alphaArrayObject);
		$shift = ArrayUtils::shift($alphaArrayObject);
		$this->assertEquals('b', $shift);
		$this->assertNotContainsEquals('b', $alphaArrayObject);
		$this->assertNotEquals($alphaArrayObject, $alphaArrayObjectCopy);
		$this->assertCount(4, $alphaArrayObject);
	}

	function testShiftWithObjectArrayObject() {
		$objectArrayObject = new ArrayObject([new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']), new ArrayObject(['a']), new ArrayObject(['d'])]);
		$objectArrayObjectCopy = new ArrayObject([new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']), new ArrayObject(['a']), new ArrayObject(['d'])]);
		$this->assertCount(5, $objectArrayObject);
		$shift = ArrayUtils::shift($objectArrayObject);
		$this->assertEquals(new ArrayObject(['b']), $shift);
		$this->assertNotContainsEquals(new ArrayObject(['b']), $objectArrayObject);
		$this->assertNotEquals($objectArrayObject, $objectArrayObjectCopy);
		$this->assertCount(4, $objectArrayObject);
	}

	function testShiftWithSingleValueArray() {
		$singleValueArray = [1];
		$shift = ArrayUtils::shift($singleValueArray);
		$this->assertEquals(1, $shift);
		$this->assertCount(0, $singleValueArray);
	}

	function testShiftWithSingleValueArrayObject() {
		$singleValueArrayObject = new ArrayObject([1]);
		$shift = ArrayUtils::shift($singleValueArrayObject);
		$this->assertEquals(1, $shift);
		$this->assertCount(0, $singleValueArrayObject);
	}

	function testShiftWithEmptyArray() {
		$emptyArray = [];
		$shift = ArrayUtils::shift($emptyArray);
		$this->assertEquals(null, $shift);
	}

	function testShiftWithEmptyArrayObject() {
		$emptyArrayObject = new ArrayObject();
		$shift = ArrayUtils::shift($emptyArrayObject);
		$this->assertEquals(null, $shift);
	}

	function testShiftRequiredWithEmptyArray() {
		$emptyArray = [];
		$this->expectException(OutOfRangeException::class);
		ArrayUtils::shift($emptyArray, true);
	}

	function testShiftRequiredWithEmptyArrayObject() {
		$emptyArrayObject = new ArrayObject();
		$this->expectException(OutOfRangeException::class);
		ArrayUtils::shift($emptyArrayObject, true);
	}


}