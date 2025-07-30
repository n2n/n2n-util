<?php

namespace n2n\util\calendar;

use n2n\util\DateParseException;
use DateTimeImmutable;
use DateTime;

class Date implements \JsonSerializable, \Stringable {
	private readonly int $day;
	private readonly int $month;
	private readonly int $year;

	function __construct(?string $arg = null) {
		$data = date_parse($arg ?? date('Y-m-d'));

		if (!empty($data['errors'])) {
			throw new DateParseException('Invalid date arg: ' . $arg . ' Reason: '
					. join(', ', $data['errors']));
		}

		if ($data['hour'] !== false || $data['minute'] !== false || $data['second'] !== false) {
			throw new DateParseException('Time present in date string: ' . $arg);
		}

		if (!checkdate($data['month'], $data['day'], $data['year'])) {
			throw new DateParseException('Invalid calendar date: ' . $arg);
		}

		$this->day = $data['day'];
		$this->month = $data['month'];
		$this->year = $data['year'];
	}

	public function getDay(): int {
		return $this->day;
	}

	public function getMonth(): int {
		return $this->month;
	}

	public function getYear(): int {
		return $this->year;
	}

	public function toSql(): string {
		return $this->__toString();
	}

	public function toDateTimeImmutable(?Time $time = null): DateTimeImmutable {
		if ($time) {
			return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->__toString(). ' '
					. $time->__toString());
		}
		return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->__toString(). ' 00:00:00');

	}

	public function toDateTime(?Time $time = null): DateTime {
		if ($time) {
			return DateTime::createFromFormat('Y-m-d H:i:s', $this->__toString() . ' '
					. $time->__toString());
		}
		return DateTime::createFromFormat('Y-m-d H:i:s', $this->__toString() . ' 00:00:00');
	}

	function jsonSerialize(): string {
		return $this->__toString();
	}

	public function __toString(): string {
		return sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day);
	}

	static function from(\DateTimeInterface $dateTime): Date {
		return new Date($dateTime->format('Y-m-d'));
	}
}