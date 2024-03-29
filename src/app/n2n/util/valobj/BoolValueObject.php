<?php

namespace n2n\util\valobj;

interface BoolValueObject {

	/**
	 * @param bool $value
	 * @throws IncompatibleValueException if passed value is invalid.
	 */
	function __construct(bool $value);

	function toValue(): bool;
}