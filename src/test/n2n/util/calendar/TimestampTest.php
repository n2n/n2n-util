<?php

namespace n2n\util\calendar;

use DateTime;
use DateTimeImmutable;
use n2n\util\DateParseException;
use PHPUnit\Framework\TestCase;

class TimestampTest extends TestCase {
	function testDateTimeConvertsWithoutLosingPrecision(): void {
		$dateTime = new DateTimeImmutable('2026-08-26 12:13:14');
		$timestamp = Timestamp::fromDateTime($dateTime);

		$this->assertSame('2026-08-26 12:13:14', (string) $timestamp);
		$this->assertEquals($dateTime, $timestamp->toDateTimeImmutable());
		$this->assertInstanceOf(DateTime::class, $timestamp->toDateTime());
		$this->assertSame('2026-08-26 12:13:14', $timestamp->toDateTime()->format('Y-m-d H:i:s'));
		$this->assertSame('2026-08-26 12:13:14', $timestamp->toSql());
		$this->assertSame('"2026-08-26 12:13:14"', json_encode($timestamp));
	}

	function testFromReturnsSameInstance(): void {
		$timestamp = Timestamp::from('2026-08-26 12:13:14');

		$this->assertSame($timestamp, Timestamp::from($timestamp));
		$this->assertNull(Timestamp::from(null));
	}

	function testConstructorRejectsInvalidValue(): void {
		$this->expectException(DateParseException::class);
		new Timestamp('not-a-date');
	}

	function testConstructorRejectsInvalidCalendarDate(): void {
		$this->expectException(DateParseException::class);
		new Timestamp('2026-02-31 12:13:14');
	}

	function testFromWrapsParseException(): void {
		$this->expectException(\InvalidArgumentException::class);
		Timestamp::from('not-a-date');
	}
}
