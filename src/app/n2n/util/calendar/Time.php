<?php

namespace n2n\util\calendar;

use n2n\util\DateParseException;
use DateTimeImmutable;
use DateTime;

class Time implements \JsonSerializable, \Stringable {
	private readonly int $hour;
	private readonly int $minute;
	private readonly int $second;


	function __construct(?string $arg = null) {
		$data = date_parse($arg ?? date('H:i:s'));

		if (!empty($data['errors'])) {
			throw new DateParseException('Invalid time arg: ' . $arg . ' Reason: '
					. join(', ', $data['errors']));
		}

		if ($data['year'] !== false || $data['month'] !== false || $data['day'] !== false) {
			throw new DateParseException('Date present in time string: ' . $arg);
		}

		$this->hour = $data['hour'];
		$this->minute = $data['minute'];
		$this->second = $data['second'];
	}

	public function getHour(): int {
		return $this->hour;
	}

	public function getMinute(): int {
		return $this->minute;
	}

	public function getSecond(): int {
		return $this->second;
	}

	public function toSql(): string {
		return $this->__toString();
	}

	public function toDateTimeImmutable(): DateTimeImmutable {
		return DateTimeImmutable::createFromFormat('H:i:s', $this->__toString());
	}

	public function toDateTime(): DateTime {
		return DateTime::createFromFormat('H:i:s', $this->__toString());
	}

	function jsonSerialize(): string {
		return $this->__toString();
	}

	public function __toString(): string {
		return sprintf('%02d:%02d:%02d', $this->hour, $this->minute, $this->second);
	}

	static function endOfDay(): Time {
		return new Time('23:59:59');
	}
}