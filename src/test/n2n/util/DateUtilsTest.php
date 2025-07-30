<?php

namespace n2n\util;

use PHPUnit\Framework\TestCase;
use DateTimeZone;
use n2n\util\calendar\Date;

class DateUtilsTest extends TestCase {

	public function testDateIntervalToSeconds() {
		$from = new \DateTime('today');
		$interval = new \DateInterval('PT1H');
		$seconds = DateUtils::dateIntervalToSeconds($from, $interval);
		$this->assertEquals(3600, $seconds);
	}

	public function testCreateDateTimeFromTimestamp() {
		$unixTimestamp = strtotime('today');
		$dateTime = DateUtils::createDateTimeFromTimestamp($unixTimestamp);
		$this->assertEquals( new \DateTime('today'), $dateTime);
	}

	public function testCreateDateTime() {
		$successfulInput1 = 'today';
		$successfulInput2 = '2025-12-24';
		$nullInput = null;
		$throwErrorInput = '+ 3d';

		$dateTime1 = DateUtils::createDateTime($successfulInput1);
		$this->assertTrue($dateTime1 instanceof \DateTime);
		$dateTime2 = DateUtils::createDateTime($successfulInput2);
		$this->assertTrue($dateTime2 instanceof \DateTime);
		$dateTime3 = DateUtils::createDateTime($nullInput);
		$this->assertNull($dateTime3);

		try {
			DateUtils::createDateTime($throwErrorInput);
			$this->fail('Exception expected');

		} catch (\Exception $e) {
			$this->assertTrue(true, 'Exception is thrown');
		}

	}

	public function testCreateDateTimeForThomas() {
		$successfulInput1 = 'today';
		$successfulInput2 = '2025-12-24';
		$nullInput = null;
		$throwErrorInput = '+ 3d';

		$dateTime1 = DateUtils::createDateTimeForThomas($successfulInput1);
		$this->assertEquals(new \DateTime('today'), $dateTime1);
		$dateTime2 = DateUtils::createDateTimeForThomas($successfulInput2);
		$this->assertEquals(new \DateTime('2025-12-24'), $dateTime2);
		$dateTime3 = DateUtils::createDateTimeForThomas($nullInput);
		$this->assertNotNull($dateTime3);
		try {
			DateUtils::createDateTimeForThomas($throwErrorInput);
			$this->fail('Exception expected');
		} catch (\Exception $e) {
			$this->assertTrue(true, 'Exception is thrown');
		}
	}

	public function testCreateDateInterval() {
		$successfulInput1 = 'P1M';
		$successfulInput2 = 'P0Y1D';
		$nullInput = null;
		$throwErrorInput = '+ 3d';

		$dateInterval1 = DateUtils::createDateInterval($successfulInput1);
		$this->assertTrue($dateInterval1 instanceof \DateInterval);
		$dateInterval2 = DateUtils::createDateInterval($successfulInput2);
		$this->assertTrue($dateInterval2 instanceof \DateInterval);
		$dateInterval3 = DateUtils::createDateInterval($nullInput);
		$this->assertNull($dateInterval3);

		try {
			DateUtils::createDateInterval($throwErrorInput);
			$this->fail('Exception expected');

		} catch (\Exception $e) {
			$this->assertTrue(true, 'Exception is thrown');
		}
	}

	public function testCreateDateTimeFromFormat() {
		$dateTime = new \DateTime('2025-11-17 08:30:45');
		$dateTimeFromFormat = DateUtils::createDateTimeFromFormat('d/m/Y H/i/s', '17/11/2025 08/30/45');
		$dateTimeFromFormat2 = DateUtils::createDateTimeFromFormat('d/m/Y H/i/s', '17/11/2025 08/30/45', new DateTimeZone('Europe/London'));
		$dateTimeFromFormat3 = DateUtils::createDateTimeFromFormat('d/m/Y H/i/s', '17/11/2025 09/30/45', new DateTimeZone('Europe/Zurich'));

		$this->assertEquals($dateTime, $dateTimeFromFormat);
		$this->assertEquals($dateTimeFromFormat2, $dateTimeFromFormat3);

	}

	public function testFormatDateTime() {
		$dateTime = new \DateTime('2025-11-17 08:30:45');

		$this->assertEquals('17.11.2025', DateUtils::formatDateTime($dateTime, 'd.m.Y'));
		$this->assertEquals('8:30', DateUtils::formatDateTime($dateTime, 'G:i'));

	}

	public function testDateTimeToIso() {
		$dateTime = new \DateTime('2025-11-17 08:30:45');
		$nullInput = null;

		$this->assertEquals('2025-11-17T08:30:45+00:00', DateUtils::dateTimeToIso($dateTime));
		$this->assertNull(DateUtils::dateTimeToIso($nullInput));
	}

	public function testIsoToDateTime() {
		$isoString = '2025-11-17T08:30:45+00:00';
		$nullInput = null;
		$dateTime = new \DateTime('2025-11-17 08:30:45');

		$this->assertEquals($dateTime, DateUtils::isoToDateTime($isoString));
		$this->assertNull(DateUtils::isoToDateTime($nullInput));
	}

	public function testTimestampToDateTime() {
		$timestampString = 1763368245;
		$nullInput = null;
		$dateTime = new \DateTime('2025-11-17 08:30:45');

		$this->assertEquals($dateTime, DateUtils::timestampToDateTime($timestampString));
		$this->assertNull(DateUtils::timestampToDateTime($nullInput));
	}

	public function testDateTimeToSql() {
		$dateTime = new \DateTime('2025-11-17 08:30:45');
		$dateTimeImmutable = new \DateTimeImmutable('2025-11-17 08:30:45');
		$nullInput = null;

		$this->assertEquals('2025-11-17 08:30:45', DateUtils::dateTimeToSql($dateTime));
		$this->assertEquals('2025-11-17 08:30:45', DateUtils::dateTimeToSql($dateTimeImmutable));
		$this->assertNull(DateUtils::dateTimeToSql($nullInput));

	}

	public function testSqlToDateTime() {
		$sqlDateTimeString = '2025-11-17 08:30:45';
		$nullInput = null;
		$throwErrorInput = '+ 3d';

		$this->assertEquals(new \DateTime('2025-11-17 08:30:45'), DateUtils::sqlToDateTime($sqlDateTimeString));
		$this->assertNull(DateUtils::sqlToDateTime($nullInput));

		try {
			DateUtils::sqlToDateTime($throwErrorInput);
			$this->fail('Exception expected');

		} catch (\InvalidArgumentException $e) {
			$this->assertTrue(true, 'Exception is thrown');
		}
	}

	public function testDateToSql() {
		$dateTime = new \DateTime('2025-11-17 08:30:45');
		$dateTimeImmutable = new \DateTimeImmutable('2025-11-17 08:30:45');
		$date = new Date('2025-11-17');
		$nullInput = null;

		$this->assertEquals('2025-11-17', DateUtils::dateToSql($dateTime));
		$this->assertEquals('2025-11-17', DateUtils::dateToSql($dateTimeImmutable));
		$this->assertEquals('2025-11-17', DateUtils::dateToSql($date));
		$this->assertNull(DateUtils::dateToSql($nullInput));
	}

	public function testStripTime() {
		$dateTime = new \DateTime('2025-11-17 08:30:45');
		$newDateTime = DateUtils::stripTime($dateTime);

		$this->assertEquals('2025-11-17 00:00:00', $newDateTime->format('Y-m-d H:i:s'));
	}

	public function testStripTimeFromImmutable() {
		$dateTimeImmutable = new \DateTimeImmutable('2025-11-17 08:30:45');
		$newDateTimeImmutable = DateUtils::stripTimeFromImmutable($dateTimeImmutable);

		$this->assertEquals('2025-11-17 00:00:00', $newDateTimeImmutable->format('Y-m-d H:i:s'));
	}

	public function testCompareDates() {
		$dateTime = new \DateTime('2025-11-17 08:30:45');
		$dateTimeImmutable = new \DateTimeImmutable('2025-11-19 08:30:45');

		$this->assertEquals(2, DateUtils::compareDates($dateTime, $dateTimeImmutable));
		$this->assertEquals(-2, DateUtils::compareDates($dateTimeImmutable, $dateTime));
		$this->assertEquals(0, DateUtils::compareDates($dateTime, $dateTime));
	}

	public function testDateInterval() {
		$dateInterval = new \DateInterval('P0Y1M1DT0H0M0S');

		$this->assertEquals($dateInterval, DateUtils::dateInterval(m: 1, d: 1));
	}

	function testMin() {
		$dateTime1 = new \DateTime('2025-11-17 08:30:45');
		$dateTime2 = new \DateTime('2025-11-18 08:30:45');
		$dateTime3 = new \DateTime('2025-11-18 08:30:46');

		$this->assertSame($dateTime1, DateUtils::min($dateTime1, $dateTime2, $dateTime3));
		$this->assertSame($dateTime1, DateUtils::min($dateTime3, $dateTime1, $dateTime2));
		$this->assertSame($dateTime1, DateUtils::min($dateTime3, $dateTime2, $dateTime1));
	}
}
