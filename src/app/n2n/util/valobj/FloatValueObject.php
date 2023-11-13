<?php

namespace n2n\util\valobj;

interface FloatValueObject {

	/**
	 * @param float $value
	 * @throws IncompatibleValueException if passed value is invalid.
	 */
	function __construct(float $value);

	function toValue(): float;
}