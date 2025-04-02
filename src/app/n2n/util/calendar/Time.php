<?php

namespace n2n\util\calendar;

use n2n\util\DateParseException;

class Time implements \JsonSerializable, \Stringable {
	private readonly int $hour;
	private readonly int $minute;
	private readonly int $second;

	function __construct(?string $arg = null) {
		$data = date_parse($arg ?? date('h:i:s'));

		if (!empty($data['errors'])) {
			throw new DateParseException('Invalid time arg: ' . $arg . ' Reason: '
					. join(', ', $data['errors']));
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

	function jsonSerialize(): string {
		return $this->__toString();
	}

	public function __toString(): string {
		return sprintf('%02d', $this->hour) . ':'
				. sprintf('%02d', $this->minute) . ':'
				. sprintf('%02d', $this->second);
	}
}