<?php

namespace n2n\util\calendar;

use PHPUnit\Framework\TestCase;
use n2n\util\DateParseException;
use DateTime;
use DateTimeImmutable;

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
		new Time('50:10:01');
	}

	function testConstructInvalidArg2(): void {
		$this->expectException(DateParseException::class);
		new Time('asfasf');
	}

	function testToSql(): void {
		$time = new Time('23:10:01');
		$this->assertEquals('23:10:01', $time->toSql());
	}

	function testToDateTime(): void {
		$time = new Time('23:10:01');
		$dateTime = $time->toDateTime();
		$this->assertInstanceOf(DateTime::class, $dateTime);
		$this->assertEquals($dateTime->format('H'), $time->getHour());
		$this->assertEquals($dateTime->format('i'), $time->getMinute());
		$this->assertEquals($dateTime->format('s'), $time->getSecond());
	}

	function testToDateTimeImmutable(): void {
		$time = new Time('23:10:01');
		$dateTimeImmutable = $time->toDateTimeImmutable();
		$this->assertInstanceOf(DateTimeImmutable::class, $dateTimeImmutable);
		$this->assertEquals($dateTimeImmutable->format('H'), $time->getHour());
		$this->assertEquals($dateTimeImmutable->format('i'), $time->getMinute());
		$this->assertEquals($dateTimeImmutable->format('s'), $time->getSecond());
	}

	function testNoArg(): void {
		$time = new Time();
		$this->assertEquals(date('H:i:s'), (string) $time);
	}

	function testEndOfDay(): void {
		$time = Time::endOfDay();
		$this->assertEquals('23:59:59', (string) $time);
	}
}