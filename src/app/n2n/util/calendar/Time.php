<?php

namespace n2n\util\calendar;

use n2n\util\DateParseException;

class Time implements \JsonSerializable, \Stringable {
	private int $hours;
	private int $minutes;
	private int $seconds;

	function __construct(?string $arg = null) {
		$data = date_parse($arg ?? date('h:i:s'));

		if (isset($data['errors'])) {
			throw new DateParseException('Invalid time arg: ' . $arg . ' Reason: '
					. join(', ', $data['errors']));
		}

		$this->hours = $data['hours'];
		$this->minutes = $data['minutes'];
		$this->seconds = $data['seconds'];
	}

	function jsonSerialize(): string {
		return $this->__toString();
	}

	public function __toString(): string {
		return $this->hours . ':' . $this->minutes . ':' . $this->seconds;
	}
}