<?php

namespace n2n\util\valobj;

interface IntValueObject {

	/**
	 * @param int $value
	 * @throws IncompatibleValueException if passed value is invalid.
	 */
	function __construct(int $value);

	function toValue(): int;
}