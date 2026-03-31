<?php

namespace n2n\util\calendar;

use n2n\util\DateParseException;
use DateTimeImmutable;
use DateTime;
use n2n\util\DateUtils;
use n2n\util\ex\ExUtils;

class PlainDateTime implements \JsonSerializable, \Stringable {
	public readonly int $day;
	public readonly int $month;
	public readonly int $year;
	public readonly int $hour;
	public readonly int $minute;
	public readonly int $second;

	/**
	 * @throws DateParseException
	 */
	function __construct(?string $arg = null) {
		$data = date_parse($arg ?? date('Y-m-d H:i:s'));

		if (!empty($data['errors'])) {
			throw new DateParseException('Invalid date arg: ' . $arg . ' Reason: '
					. join(', ', $data['errors']));
		}

		if (!checkdate($data['month'], $data['day'], $data['year'])) {
			throw new DateParseException('Invalid calendar date: ' . $arg);
		}

		$this->year = $data['year'];
		$this->month = $data['month'];
		$this->day = $data['day'];
		$this->hour = $data['hour'];
		$this->minute = $data['minute'];
		$this->second = $data['second'];
	}


	public function toSql(): string {
		return $this->__toString();
	}

	function toDate(): Date {
		return Date::fromDigits($this->year, $this->month, $this->day);
	}

	function toTime(): Time {
		return Time::fromDigits($this->hour, $this->minute, $this->second);
	}

	public function toDateTimeImmutable(): DateTimeImmutable {
		return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->__toString());
	}

	public function toDateTime(): DateTime {
		return DateTime::createFromFormat('Y-m-d H:i:s', $this->__toString());
	}

	function diff(PlainDateTime|\DateTimeInterface $date, bool $absolute = false): \DateInterval {
		return $this->toDateTime()->diff(ExUtils::try(fn () => DateUtils::createDateTime($date)), $absolute);
	}

	function spaceshipCompareWith(PlainDateTime|\DateTimeInterface $date): int {
		return $this->toDateTimeImmutable() <=> PlainDateTime::from($date)->toDateTimeImmutable();
	}

	function isLessThan(PlainDateTime|\DateTimeInterface $date): bool {
		return $this->spaceshipCompareWith($date) === -1;
	}

	function isLessThanOrEqualTo(PlainDateTime|\DateTimeInterface $date): bool {
		return $this->spaceshipCompareWith($date) <= 0;
	}

	function isGreaterThan(PlainDateTime|\DateTimeInterface $date): bool {
		return $this->spaceshipCompareWith($date) === 1;
	}

	function isGreaterThanOrEqualTo(PlainDateTime|\DateTimeInterface $date): bool {
		return $this->spaceshipCompareWith($date) >= 0;
	}

	function isEqualTo(PlainDateTime|\DateTimeInterface $date): bool {
		return $this->spaceshipCompareWith($date) === 0;
	}

	function jsonSerialize(): string {
		return $this->__toString();
	}

	public function __toString(): string {
		return sprintf('%04d-%02d-%02d %02d:%02d:%02d',
				$this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second);
	}

	static function from(\DateTimeInterface|PlainDateTime|Date|Time|string|null $dateTime): ?PlainDateTime {
		if ($dateTime === null) {
			return null;
		}
		if (is_string($dateTime)) {
			try {
				return new PlainDateTime($dateTime);
			} catch (DateParseException $e) {
				throw new \InvalidArgumentException($e->getMessage(), previous: $e);
			}
		}
		if ($dateTime instanceof PlainDateTime) {
			return $dateTime;
		}
		if ($dateTime instanceof Date) {
			$dateTime = $dateTime->toDateTimeImmutable();
		}
		if ($dateTime instanceof Time) {
			$dateTime = $dateTime->toDateTimeImmutable();
		}
		return self::from($dateTime->format('Y-m-d H:i:s'));
	}

	static function fromDigits(int $year, int $month, int $day, int $hour, int $minute, int $second): PlainDateTime {
		return self::from($year . '-' . $month . '-' . $day .' '. $hour . ':' . $minute . ':' . $second);
	}
}
