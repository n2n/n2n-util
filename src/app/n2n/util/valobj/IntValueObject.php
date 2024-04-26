<?php

namespace n2n\util\valobj;

interface IntValueObject {

	/**
	 * @param int $value
	 * @throws IllegalValueException if passed value is invalid.
	 */
	function __construct(int $value);

	function toValue(): int;
}