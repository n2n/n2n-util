<?php

namespace n2n\util\col;

use PHPUnit\Framework\TestCase;
use ArrayObject;

class ArrayUtilsTest extends TestCase {


	protected function setUp(): void {
	}

	function testContainsStrict() {
		$arr = [1, 3, 5];
		$this->assertTrue(ArrayUtils::contains($arr, 1, true));
		$this->assertFalse(ArrayUtils::contains($arr, '1', true));
		$this->assertFalse(ArrayUtils::contains($arr, 2, true));

		$arrObj = new ArrayObject([1, 3, 5]);
		$this->assertTrue(ArrayUtils::contains($arrObj, 1, true));
		$this->assertFalse(ArrayUtils::contains($arrObj, '1', true));
		$this->assertFalse(ArrayUtils::contains($arrObj, 2, true));

		$objectArrayObject = new ArrayObject([new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']),
				new ArrayObject(['a']), new ArrayObject(['d'])]);
		$this->assertTrue(ArrayUtils::contains($objectArrayObject,
				ArrayUtils::first($objectArrayObject), true));
		$this->assertFalse(ArrayUtils::contains($objectArrayObject, new ArrayObject(['c']), true));
	}
	function testContainsLenient() {
		$arr = [1, 3, 5];
		$this->assertTrue(ArrayUtils::contains($arr, 1, false));
		$this->assertTrue(ArrayUtils::contains($arr, '1', false));
		$this->assertFalse(ArrayUtils::contains($arr, 2, false));

		$arrObj = new ArrayObject([1, 3, 5]);
		$this->assertTrue(ArrayUtils::contains($arrObj, 1, false));
		$this->assertTrue(ArrayUtils::contains($arrObj, '1', false));
		$this->assertFalse(ArrayUtils::contains($arrObj, 2, false));

		$objectArrayObject = new ArrayObject([new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']),
				new ArrayObject(['a']), new ArrayObject(['d'])]);
		$this->assertTrue(ArrayUtils::contains($objectArrayObject,
				ArrayUtils::first($objectArrayObject), false));
		$this->assertTrue(ArrayUtils::contains($objectArrayObject, new ArrayObject(['c']), false));
	}

	function testUniqueAddStrict() {
		$arr = [1, 3, 5];
		$this->assertFalse(ArrayUtils::uniqueAdd($arr, 1, true));
		$this->assertTrue(ArrayUtils::uniqueAdd($arr, '1', true));
		$this->assertTrue(ArrayUtils::uniqueAdd($arr, 2, true));

		$arrObj = new ArrayObject([1, 3, 5]);
		$this->assertFalse(ArrayUtils::uniqueAdd($arrObj, 1, true));
		$this->assertTrue(ArrayUtils::uniqueAdd($arrObj, '1', true));
		$this->assertTrue(ArrayUtils::uniqueAdd($arrObj, 2, true));

		$objectArrayObject = new ArrayObject([new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']),
				new ArrayObject(['a']), new ArrayObject(['d'])]);
		$this->assertFalse(ArrayUtils::uniqueAdd($objectArrayObject,
				ArrayUtils::first($objectArrayObject), true));
		$this->assertTrue(ArrayUtils::uniqueAdd($objectArrayObject, new ArrayObject(['c']), true));
	}
	function testUniqueAddLenient() {
		$arr = [1, 3, 5];
		$this->assertFalse(ArrayUtils::uniqueAdd($arr, 1, false));
		$this->assertFalse(ArrayUtils::uniqueAdd($arr, '1', false));
		$this->assertTrue(ArrayUtils::uniqueAdd($arr, 2, false));

		$arrObj = new ArrayObject([1, 3, 5]);
		$this->assertFalse(ArrayUtils::uniqueAdd($arrObj, 1, false));
		$this->assertFalse(ArrayUtils::uniqueAdd($arrObj, '1', false));
		$this->assertTrue(ArrayUtils::uniqueAdd($arrObj, 2, false));

		$objectArrayObject = new ArrayObject([new ArrayObject(['b']), new ArrayObject(['e']), new ArrayObject(['c']),
				new ArrayObject(['a']), new ArrayObject(['d'])]);
		$this->assertFalse(ArrayUtils::uniqueAdd($objectArrayObject,
				ArrayUtils::first($objectArrayObject), false));
		$this->assertFalse(ArrayUtils::uniqueAdd($objectArrayObject, new ArrayObject(['c']), false));
	}
}