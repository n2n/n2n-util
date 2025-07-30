<?php

namespace n2n\util\calendar;

use PHPUnit\Framework\TestCase;
use n2n\util\DateParseException;
use DateTime;
use DateTimeImmutable;
use n2n\util\DateUtils;

class DateTest extends TestCase {

	function testConstruct(): void {
		$date = new Date('2023-10-01');
		$this->assertEquals(2023, $date->getYear());
		$this->assertEquals(10, $date->getMonth());
		$this->assertEquals(1, $date->getDay());
		$this->assertEquals('2023-10-01', $date->__toString());
	}

	function testConstructInvalidArg(): void {
		$this->expectException(DateParseException::class);
		new Date('2020-02-30'); //30 february is not possible
	}

	function testConstructInvalidArg2(): void {
		$this->expectException(DateParseException::class);
		new Date('asfasf');
	}

	function testToSql(): void {
		$date = new Date('2023-10-01');
		$this->assertEquals('2023-10-01', $date->toSql());
	}

	function testToDateTime(): void {
		$date = new Date('2023-10-01');
		$dateTime = $date->toDateTime();
		$this->assertInstanceOf(DateTime::class, $dateTime);
		$this->assertEquals($dateTime->format('Y'), $date->getYear());
		$this->assertEquals($dateTime->format('m'), $date->getMonth());
		$this->assertEquals($dateTime->format('d'), $date->getDay());
	}

	function testToDateTimeWithTime(): void {
		$date = new Date('2023-10-01');
		$dateTime = $date->toDateTime(new Time('01:02:03'));
		$this->assertEquals('2023-10-01 01:02:03', DateUtils::dateTimeToSql($dateTime));
	}

	function testToDateTimeImmutable(): void {
		$date = new Date('2023-10-01');
		$dateTimeImmutable = $date->toDateTimeImmutable();
		$this->assertInstanceOf(DateTimeImmutable::class, $dateTimeImmutable);
		$this->assertEquals($dateTimeImmutable->format('Y'), $date->getYear());
		$this->assertEquals($dateTimeImmutable->format('m'), $date->getMonth());
		$this->assertEquals($dateTimeImmutable->format('d'), $date->getDay());
	}

	function testToDateTimeImmutableWithTime(): void {
		$date = new Date('2023-10-01');
		$dateTime = $date->toDateTimeImmutable(new Time('01:02:03'));
		$this->assertEquals('2023-10-01 01:02:03', DateUtils::dateTimeToSql($dateTime));
	}

	function testNoArg(): void {
		$date = new Date();
		$this->assertEquals(date('Y-m-d'), (string) $date);
	}

	function testFromDateTime(): void {
		$dateTime = new DateTime('2023-10-01 01:01:01');
		$date = new Date('2023-10-01');
		$this->assertEquals(Date::from($dateTime), (string) $date);
	}

	function testFromDateTimeImmutable(): void {
		$dateTime = new DateTimeImmutable('2023-10-01 01:01:01');
		$date = new Date('2023-10-01');
		$this->assertEquals(Date::from($dateTime), (string) $date);
	}
}