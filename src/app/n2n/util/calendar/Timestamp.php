<?php

namespace n2n\util\calendar;

use DateTimeImmutable;
use DateTime;
use DateTimeInterface;
use n2n\util\DateParseException;
use n2n\util\DateUtils;
use n2n\util\ex\ExUtils;

final class Timestamp implements \JsonSerializable, \Stringable {
	private readonly string $value;

	/**
	 * @throws DateParseException if you do not wish to handle this checked exception use {@link Timestamp::from()}.
	 */
	function __construct(?string $arg = null) {
		$arg ??= date(DateUtils::SQL_DATE_TIME_FORMAT);
		$dateTime = DateUtils::createDateTimeImmutableFromFormat(DateUtils::SQL_DATE_TIME_FORMAT, $arg);

		if ($dateTime->format(DateUtils::SQL_DATE_TIME_FORMAT) !== $arg) {
			throw new DateParseException('Invalid timestamp: ' . $arg);
		}
		$this->value = $arg;
	}

	public function toSql(): string {
		return $this->__toString();
	}

	/**
	 * @throws DateParseException
	 */
	public function toDateTimeImmutable(): DateTimeImmutable {
		return DateUtils::createDateTimeImmutableFromFormat(DateUtils::SQL_DATE_TIME_FORMAT, $this->value);
	}

	/**
	 * @throws DateParseException
	 */
	public function toDateTime(): DateTime {
		return DateUtils::createDateTimeFromFormat(DateUtils::SQL_DATE_TIME_FORMAT, $this->value);
	}

	function jsonSerialize(): string {
		return $this->__toString();
	}

	public function __toString(): string {
		return $this->value;
	}

	static function now(): Timestamp {
		return ExUtils::try(fn() => new Timestamp());
	}

	static function from(DateTimeInterface|Timestamp|Date|string|null $dateTime): ?Timestamp {
		if ($dateTime === null) {
			return null;
		}
		if (is_string($dateTime)) {
			try {
				return new Timestamp($dateTime);
			} catch (DateParseException $e) {
				throw new \InvalidArgumentException($e->getMessage(), previous: $e);
			}
		}
		if ($dateTime instanceof Timestamp) {
			return $dateTime;
		}
		if ($dateTime instanceof Date) {
			$dateTime = $dateTime->toDateTimeImmutable();
		}

		return self::from($dateTime->format(DateUtils::SQL_DATE_TIME_FORMAT));
	}
}
