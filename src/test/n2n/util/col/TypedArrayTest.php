<?php

namespace n2n\util\col;

use PHPUnit\Framework\TestCase;
use n2n\util\col\mock\ObjMockArray;
use n2n\util\col\mock\ObjMock;
use OutOfBoundsException;
use n2n\util\col\mock\ObjMockKeyArray;

class TypedArrayTest extends TestCase {

	function testValidScalarKeyArrayAccess() {
		$arr = new ObjMockArray();
		$arr['key1'] = new ObjMock('value1');
		$arr[2] = new ObjMock('value2');

		$this->assertEquals(new ObjMock('value1'), $arr['key1']);
		$this->assertEquals(new ObjMock('value2'), $arr[2]);
	}

	function testNonExistingScalarKeyArrayAccess() {
		$arr = new ObjMockArray();
		$arr['key1'] = new ObjMock('value1');

		$this->assertEquals(new ObjMock('value1'), $arr['key1'] ?? 'holeradio');
		$this->assertSame('holeradio', $arr['key2'] ?? 'holeradio');

		$this->expectException(OutOfBoundsException::class);
		$arr['key2'];
	}

	function testInvalidScalarKeyArrayAccessGet() {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Passed array key is invalid: ' . ObjMock::class);

		$arr = new ObjMockArray();
		$this->assertNull($arr[new ObjMock('value1')]);
	}

	function testScalarKeyCountAndIsEmpty() {
		$arr = new ObjMockArray();
		$arr['key1'] = new ObjMock('value1');
		$arr[2] = new ObjMock('value2');

		$this->assertSame(2, $arr->count());

		$arr->clear();

		$this->assertSame(0, $arr->count());
	}

	function testScalarKeyIterate() {
		$arr = new ObjMockArray();
		$arr['key1'] = new ObjMock('value1');
		$arr[2] = new ObjMock('value2');

		$i = 0;
		foreach ($arr as $key => $value) {
			if ($i++ === 0) {
				$this->assertSame('key1', $key);
				$this->assertEquals(new ObjMock('value1'), $value);
			} else {
				$this->assertSame(2, $key);
				$this->assertEquals(new ObjMock('value2'), $value);
			}
		}
	}



	function testValidObjKeyArrayAccess() {
		$arr = new ObjMockKeyArray();
		$key1 = new ObjMock('key1');
		$key2 = new ObjMock('key2');
		$arr[$key1] = 'value1';
		$arr[$key2] = 2;

		$this->assertSame('value1', $arr[$key1]);
		$this->assertSame('2', $arr[$key2]);
	}

	function testNonExistingObjKeyArrayAccess() {
		$arr = new ObjMockKeyArray();
		$key1 = new ObjMock('key1');
		$key2 = new ObjMock('key2');
		$arr[$key1] = 'value1';

		$this->assertEquals('value1', $arr[$key1] ?? 'holeradio');
		$this->assertSame('holeradio', $arr[$key2] ?? 'holeradio');

		$this->expectException(OutOfBoundsException::class);
		$arr[$key2];
	}

	function testInvalidObjKeyArrayAccessGet() {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Passed array key is invalid: key1');

		$arr = new ObjMockKeyArray();
		$arr['key1'];
	}

	function testObjKeyCountAndIsEmpty() {
		$arr = new ObjMockKeyArray();
		$key1 = new ObjMock('key1');
		$key2 = new ObjMock('key2');
		$arr[$key1] = 'value1';
		$arr[$key2] = 2;

		$this->assertSame(2, $arr->count());

		$arr->clear();

		$this->assertSame(0, $arr->count());
	}

	function testObjKeyIterate() {
		$arr = new ObjMockKeyArray();
		$key1 = new ObjMock('key1');
		$key2 = new ObjMock('key2');
		$arr[$key1] = 'value1';
		$arr[$key2] = 2;

		$i = 0;
		foreach ($arr as $key => $value) {
			if ($i++ === 0) {
				$this->assertEquals($key1, $key);
				$this->assertSame('value1', $value);
			} else {
				$this->assertEquals($key2, $key);
				$this->assertSame('2', $value);
			}
		}
	}



	function testInvalidKeyArrayAccessGet() {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Passed array key is invalid: ' . ObjMock::class);

		$arr = new ObjMockArray();
		$arr[new ObjMock('value1')];
	}

	function testInvalidKeyArrayAccessSet() {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Passed array key is invalid: ' . ObjMock::class);

		$arr = new ObjMockArray();
		$arr[new ObjMock('value1')] = new ObjMock('value1');
	}

	function testInvalidValueArrayAccessSet() {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Passed array value is invalid: DateTime');

		$arr = new ObjMockArray();
		$arr['key1'] = new \DateTime();
	}
}
