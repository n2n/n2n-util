<?php

namespace n2n\util\calendar;

use PHPUnit\Framework\TestCase;
use n2n\util\DateParseException;

class TimeTest extends TestCase {

	function testConstruct(): void {
		$time = new Time('23:10:01');
		$this->assertEquals(23, $time->getHour());
		$this->assertEquals(10, $time->getMinute());
		$this->assertEquals(1, $time->getSecond());
		$this->assertEquals('23:10:01', $time->__toString());
	}

	function testConstructInvalidArg(): void {
		$this->expectException(DateParseException::class);
		$time = new Time('50:10:01');
	}

	function testConstructInvalidArg2(): void {
		$this->expectException(DateParseException::class);
		$time = new Time('asfasf');
	}
}