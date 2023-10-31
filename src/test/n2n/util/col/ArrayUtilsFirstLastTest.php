<?php

namespace n2n\util\col;

use PHPUnit\Framework\TestCase;
use ArrayObject;

class ArrayUtilsFirstLastTest extends TestCase {
	protected function setUp(): void {
	}

	//get First value of Array/ArrayLike
	function testFirstWithNumArray() {
		$numArray = [2, 5, 3, 1, 4];
		$first = ArrayUtils::first($numArray);
		$this->assertEquals(2, $first);
	}

	function testFirstWithAlphaArray() {
		$alphaArray = ['b', 'e', 'c', 'a', 'd'];
		$first = ArrayUtils::first($alphaArray);
		$this->assertEquals('b', $first);
	}

	function testFirstWithObjectArray() {
		$objectArray = [new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']), new ArrayObject(['a']), new ArrayObject(['d'])];
		$first = ArrayUtils::first($objectArray);
		$this->assertEquals(new ArrayObject(['b']), $first);
	}

	function testFirstWithNumArrayObject() {
		$numArrayObject = new ArrayObject([2, 5, 3, 1, 4]);
		$first = ArrayUtils::first($numArrayObject);
		$this->assertEquals(2, $first);
	}

	function testFirstWithAlphaArrayObject() {
		$alphaArrayObject = new ArrayObject(['b', 'e', 'c', 'a', 'd']);
		$first = ArrayUtils::first($alphaArrayObject);
		$this->assertEquals('b', $first);
	}

	function testFirstWithObjectArrayObject() {
		$objectArrayObject = new ArrayObject([new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']), new ArrayObject(['a']), new ArrayObject(['d'])]);
		$first = ArrayUtils::first($objectArrayObject);
		$this->assertEquals(new ArrayObject(['b']), $first);
	}

	function testFirstWithEmptyArray() {
		$emptyArray = [];
		$first = ArrayUtils::first($emptyArray);
		$this->assertEquals(null, $first);
	}

	function testFirstWithEmptyObject() {
		$emptyArrayObject = new ArrayObject();
		$first = ArrayUtils::first($emptyArrayObject);
		$this->assertEquals(null, $first);
	}


	//get Last value of Array/ArrayLike
	function testLastWithNumArray() {
		$numArray = [2, 5, 3, 1, 4];
		$last = ArrayUtils::last($numArray);
		$this->assertEquals(4, $last);
	}

	function testLastWithAlphaArray() {
		$alphaArray = ['b', 'e', 'c', 'a', 'd'];
		$last = ArrayUtils::last($alphaArray);
		$this->assertEquals('d', $last);
	}

	function testLastWithObjectArray() {
		$objectArray = [new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']), new ArrayObject(['a']), new ArrayObject(['d'])];
		$last = ArrayUtils::last($objectArray);
		$this->assertEquals(new ArrayObject(['d']), $last);
	}

	function testLastWithNumArrayObject() {
		$numArrayObject = new ArrayObject([2, 5, 3, 1, 4]);
		$last = ArrayUtils::last($numArrayObject);
		$this->assertEquals(4, $last);
	}

	function testLastWithAlphaArrayObject() {
		$alphaArrayObject = new ArrayObject(['b', 'e', 'c', 'a', 'd']);
		$last = ArrayUtils::last($alphaArrayObject);
		$this->assertEquals('d', $last);
	}

	function testLastWithObjectArrayObject() {
		$objectArrayObject = new ArrayObject([new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']), new ArrayObject(['a']), new ArrayObject(['d'])]);
		$last = ArrayUtils::last($objectArrayObject);
		$this->assertEquals(new ArrayObject(['d']), $last);
	}

	function testLastWithEmptyArray() {
		$emptyArray = [];
		$last = ArrayUtils::last($emptyArray);
		$this->assertEquals(null, $last);
	}

	function testLastWithEmptyObject() {
		$emptyArrayObject = new ArrayObject();
		$last = ArrayUtils::last($emptyArrayObject);
		$this->assertEquals(null, $last);
	}
}