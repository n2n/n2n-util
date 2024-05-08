<?php

namespace n2n\util;

use PHPUnit\Framework\TestCase;

class BinaryUtilsTest extends TestCase {

	function testIgbinaryUnserialize() {
		$this->markTestSkipped('ci bob does not support igbinary yet.');

		$this->assertTrue(['hoi' => 1] === BinaryUtils::igbinaryUnserialize(igbinary_serialize(['hoi' => 1])));
	}

	function testIgbinaryUnserializeNullCase()  {
		$this->markTestSkipped('ci bob does not support igbinary yet.');

		trigger_error('populate error_get_last()', E_USER_WARNING);
		$this->assertNotNull(error_get_last());

		$this->assertTrue(false === BinaryUtils::igbinaryUnserialize(igbinary_serialize(false)));
		$this->assertTrue(null === BinaryUtils::igbinaryUnserialize(igbinary_serialize(null)));
	}

	function testIgbinaryUnserializeException() {
		$this->markTestSkipped('ci bob does not support igbinary yet.');

		$this->expectException(UnserializationFailedException::class);
		$this->expectExceptionMessage('igbinary_unserialize_header');

		var_dump(BinaryUtils::igbinaryUnserialize('hoi totally wrong'));
	}
}