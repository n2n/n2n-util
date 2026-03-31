<?php

namespace n2n\util\calendar;

use PHPUnit\Framework\TestCase;
use n2n\util\DateParseException;
use DateTime;
use DateTimeImmutable;

class PlainDateTimeTest extends TestCase {
	/**
	 * this will test  {@link PlainDateTime}
	 */
	function testConstruct(): void {
		$date = new PlainDateTime('2023-10-01 16:55:40');
		$this->assertEquals(2023, $date->year);
		$this->assertEquals(10, $date->month);
		$this->assertEquals(1, $date->day);
		$this->assertEquals('2023-10-01 16:55:40', $date->__toString());
	}

	function testConstructInvalidArg(): void {
		$this->expectException(DateParseException::class);
		new PlainDateTime('2020-02-30'); //30 february is not possible
	}

	function testConstructInvalidArg2(): void {
		$this->expectException(DateParseException::class);
		new PlainDateTime('asfasf');
	}

	function testToSql(): void {
		$date = new PlainDateTime('2023-10-01 16:55:40');
		$this->assertEquals('2023-10-01 16:55:40', $date->toSql());
	}

	function testToDateTime(): void {
		$date = new PlainDateTime('2023-10-01 16:55:40');
		$dateTime = $date->toDateTime();
		$this->assertInstanceOf(DateTime::class, $dateTime);
		$this->assertEquals($dateTime->format('Y'), $date->year);
		$this->assertEquals($dateTime->format('m'), $date->month);
		$this->assertEquals($dateTime->format('d'), $date->day);
		$this->assertEquals($dateTime->format('H'), $date->hour);
		$this->assertEquals($dateTime->format('i'), $date->minute);
		$this->assertEquals($dateTime->format('s'), $date->second);
	}

	function testToDateTimeImmutable(): void {
		$date = new PlainDateTime('2023-10-01 16:55:40');
		$dateTimeImmutable = $date->toDateTimeImmutable();
		$this->assertInstanceOf(DateTimeImmutable::class, $dateTimeImmutable);
		$this->assertEquals($dateTimeImmutable->format('Y'), $date->year);
		$this->assertEquals($dateTimeImmutable->format('m'), $date->month);
		$this->assertEquals($dateTimeImmutable->format('d'), $date->day);
		$this->assertEquals($dateTimeImmutable->format('H'), $date->hour);
		$this->assertEquals($dateTimeImmutable->format('i'), $date->minute);
		$this->assertEquals($dateTimeImmutable->format('s'), $date->second);
	}

	function testNoArg(): void {
		$date = new PlainDateTime();
		$this->assertEquals(date('Y-m-d H:i:s'), (string) $date);
	}

	function testFromDateTime(): void {
		$dateTime = new DateTime('2023-10-01 16:55:40');
		$date = new PlainDateTime('2023-10-01 16:55:40');
		$this->assertEquals(PlainDateTime::from($dateTime), (string) $date);
	}

	function testFromDateTimeImmutable(): void {
		$dateTime = new DateTimeImmutable('2023-10-01 16:55:40');
		$date = new PlainDateTime('2023-10-01 16:55:40');
		$this->assertEquals(PlainDateTime::from($dateTime), (string) $date);
	}

	function testFromDate(): void {
		$date = new PlainDateTime('2023-10-01 16:55:40');
		$this->assertEquals(PlainDateTime::from($date), (string) $date);
	}

	function testDiff(): void {
		$date = new PlainDateTime('2023-01-01 16:55:40');
		$date2 = new PlainDateTime('2025-04-07 08:23:18');
		$dateInterval = $date->diff($date2);

		$this->assertSame(826, $dateInterval->days);
		$this->assertSame(2, $dateInterval->y);
		$this->assertSame(3, $dateInterval->m);
		$this->assertSame(5, $dateInterval->d);
		$this->assertSame(15, $dateInterval->h);
		$this->assertSame(27, $dateInterval->i);
		$this->assertSame(38, $dateInterval->s);
		$this->assertSame(0.0, $dateInterval->f);
		$this->assertSame(0, $dateInterval->invert);
	}

	function testDiffAbsolute(): void {
		$date = new PlainDateTime('2025-04-07 16:55:40');
		$date2 = new DateTimeImmutable('2023-01-01 08:23:18');
		$dateInterval = $date->diff($date2, true);

		$this->assertSame(827, $dateInterval->days);
		$this->assertSame(2, $dateInterval->y);
		$this->assertSame(3, $dateInterval->m);
		$this->assertSame(0, $dateInterval->invert);

		$dateInterval = $date->diff($date2);
		$this->assertSame(827, $dateInterval->days);
		$this->assertSame(2, $dateInterval->y);
		$this->assertSame(3, $dateInterval->m);
		$this->assertSame(1, $dateInterval->invert);
	}

	function testSpaceshipCompareWith(): void {
		$date = new PlainDateTime('2023-01-01');
		$date1 = new PlainDateTime('2025-04-07');
		$date2 = new DateTimeImmutable('2023-01-01');
		$date3 = new DateTime('2021-06-07');

		$this->assertSame(-1, $date->spaceshipCompareWith($date1));
		$this->assertSame(0, $date->spaceshipCompareWith($date2));
		$this->assertSame(1, $date->spaceshipCompareWith($date3));
	}

	function testIsLessThan(): void {
		$date = new PlainDateTime('2023-01-01');
		$date1 = new PlainDateTime('2025-04-07');
		$date2 = new DateTimeImmutable('2023-01-01');
		$date3 = new DateTime('2021-06-07');

		$this->assertTrue($date->isLessThan($date1));
		$this->assertFalse($date->isLessThan($date2));
		$this->assertFalse($date->isLessThan($date3));
	}

	function testIsLessThanOrEqualTo(): void {
		$date = new PlainDateTime('2023-01-01');
		$date1 = new PlainDateTime('2025-04-07');
		$date2 = new DateTimeImmutable('2023-01-01');
		$date3 = new DateTime('2021-06-07');

		$this->assertTrue($date->isLessThanOrEqualTo($date1));
		$this->assertTrue($date->isLessThanOrEqualTo($date2));
		$this->assertFalse($date->isLessThanOrEqualTo($date3));
	}

	function testIsGreaterThan(): void {
		$date = new PlainDateTime('2023-01-01');
		$date1 = new PlainDateTime('2025-04-07');
		$date2 = new DateTimeImmutable('2023-01-01');
		$date3 = new DateTime('2021-06-07');

		$this->assertFalse($date->isGreaterThan($date1));
		$this->assertFalse($date->isGreaterThan($date2));
		$this->assertTrue($date->isGreaterThan($date3));
	}

	function testIsGreaterThanOrEqualTo(): void {
		$date = new PlainDateTime('2023-01-01');
		$date1 = new PlainDateTime('2025-04-07');
		$date2 = new DateTimeImmutable('2023-01-01');
		$date3 = new DateTime('2021-06-07');

		$this->assertFalse($date->isGreaterThanOrEqualTo($date1));
		$this->assertTrue($date->isGreaterThanOrEqualTo($date2));
		$this->assertTrue($date->isGreaterThanOrEqualTo($date3));
	}

	function testNullable(): void {
		$this->assertNull(PlainDateTime::from(null));
	}

	function testFromDigits(): void {
		$date = new PlainDateTime('2023-01-01 16:55:40');
		$date1 = PlainDateTime::fromDigits(2023,1,1,16,55,40);
		$this->assertEquals($date, $date1);
	}

	function testToDate(): void {
		$date = new PlainDateTime('2023-01-01 16:55:40')->toDate();
		$date1 = new Date('2023-01-01');
		$this->assertEquals($date, $date1);
	}

	function testToTime(): void {
		$date = new PlainDateTime('2023-01-01 16:55:40')->toTime();
		$date1 = new Time('16:55:40');
		$this->assertEquals($date, $date1);
	}

}