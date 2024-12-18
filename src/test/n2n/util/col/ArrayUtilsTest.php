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

	function testDiffWalk() {
		// Test Case 1: Only Additions with Arrays (Strict)
		$original = ['apple', 'banana'];
		$new = ['apple', 'banana', 'cherry', 'date'];
		$added = [];
		$removed = [];
		ArrayUtils::diffWalk($original, $new, function($item) use (&$added) { $added[] = $item; },
				function($item) use (&$removed) { $removed[] = $item; }, true);

		$this->assertEquals(['cherry', 'date'], $added, "Added items should be ['cherry', 'date']");
		$this->assertEmpty($removed, "No items should be removed");

		// Test Case 2: Only Removals with Arrays (Strict)
		$original = ['apple', 'banana', 'cherry', 'date'];
		$new = ['apple', 'banana'];
		$added = [];
		$removed = [];
		ArrayUtils::diffWalk($original, $new, function($item) use (&$added) { $added[] = $item; },
				function($item) use (&$removed) { $removed[] = $item; }, true);

		$this->assertEmpty($added, "No items should be added");
		$this->assertEquals(['cherry', 'date'], $removed, "Removed items should be ['cherry', 'date']");

		// Test Case 3: Both Additions and Removals with Arrays (Strict)
		$original = ['apple', 'banana', 'cherry'];
		$new = ['banana', 'date', 'elderberry'];
		$added = [];
		$removed = [];
		ArrayUtils::diffWalk($original, $new, function($item) use (&$added) { $added[] = $item; },
				function($item) use (&$removed) { $removed[] = $item; }, true);

		$this->assertEquals(['date', 'elderberry'], $added, "Added items should be ['date', 'elderberry']");
		$this->assertEquals(['apple', 'cherry'], $removed, "Removed items should be ['apple', 'cherry']");

		// Test Case 4: No Changes with Arrays (Strict)
		$original = ['apple', 'banana', 'cherry'];
		$new = ['apple', 'banana', 'cherry'];
		$added = [];
		$removed = [];
		ArrayUtils::diffWalk($original,$new, function($item) use (&$added) { $added[] = $item; },
				function($item) use (&$removed) { $removed[] = $item; }, true);

		$this->assertEmpty($added, "No items should be added");
		$this->assertEmpty($removed, "No items should be removed");

		// Test Case 5: Non-Strict Comparison with Arrays
		$original = [1, 2, 3];
		$new = ['1', 2, '3', 4];
		$added = [];
		$removed = [];
		ArrayUtils::diffWalk($original, $new, function($item) use (&$added) { $added[] = $item; },
				function($item) use (&$removed) { $removed[] = $item; }, false
		);

		// In non-strict comparison, '1' == 1 and '3' == 3, so only 4 is added and nothing is removed
		$this->assertEquals([4], $added, "Added items should be [4]");
		$this->assertEmpty($removed, "No items should be removed");

		// Test Case 6: Strict Comparison with Different Types
		$original = [1, 2, 3];
		$new = ['1', 2, '3', 4];
		$added = [];
		$removed = [];
		ArrayUtils::diffWalk($original, $new, function($item) use (&$added) { $added[] = $item; },
				function($item) use (&$removed) { $removed[] = $item; }, true);

		// In strict comparison, '1' !== 1 and '3' !== 3, so '1', '3', 4 are added and 1, 3 are removed
		$this->assertEquals(['1', '3', 4], $added, "Added items should be ['1', '3', 4]");
		$this->assertEquals([1, 3], $removed, "Removed items should be [1, 3]");

		// Test Case 7: Using ArrayObject Instances
		$original = new ArrayObject(['apple', 'banana']);
		$new = new ArrayObject(['banana', 'cherry']);
		$added = [];
		$removed = [];
		ArrayUtils::diffWalk($original, $new, function($item) use (&$added) { $added[] = $item; },
				function($item) use (&$removed) { $removed[] = $item; }, true);

		$this->assertEquals(['cherry'], $added, "Added items should be ['cherry']");
		$this->assertEquals(['apple'], $removed, "Removed items should be ['apple']");

		// Test Case 8: Empty Original Collection
		$original = [];
		$new = ['apple', 'banana'];
		$added = [];
		$removed = [];
		ArrayUtils::diffWalk($original, $new, function($item) use (&$added) { $added[] = $item; },
				function($item) use (&$removed) { $removed[] = $item; }, true);

		$this->assertEquals(['apple', 'banana'], $added, "Added items should be ['apple', 'banana']");
		$this->assertEmpty($removed, "No items should be removed");

		// Test Case 9: Empty New Collection
		$original = ['apple', 'banana'];
		$new = [];
		$added = [];
		$removed = [];
		ArrayUtils::diffWalk($original, $new, function($item) use (&$added) { $added[] = $item; },
				function($item) use (&$removed) { $removed[] = $item; }, true);

		$this->assertEmpty($added, "No items should be added");
		$this->assertEquals(['apple', 'banana'], $removed, "Removed items should be ['apple', 'banana']");

		// Test Case 10: Using ArrayObject Instances with Complex Data Types
		$object1 = (object)['id' => 1, 'name' => 'Alice'];
		$object2 = (object)['id' => 2, 'name' => 'Bob'];
		$object3 = (object)['id' => 3, 'name' => 'Charlie'];

		$original = new ArrayObject([$object1, $object2]);
		$new = new ArrayObject([$object2, $object3]);
		$added = [];
		$removed = [];

		ArrayUtils::diffWalk($original, $new, function($item) use (&$added) { $added[] = $item; },
				function($item) use (&$removed) { $removed[] = $item; }, true);

		$this->assertCount(1, $added, "One object should be added");
		$this->assertSame($object3, $added[0], "Added object should be object3");
		$this->assertCount(1, $removed, "One object should be removed");
		$this->assertSame($object1, $removed[0], "Removed object should be object1");
	}
}