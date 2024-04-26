<?php

namespace n2n\util\valobj;

interface FloatValueObject {

	/**
	 * @param float $value
	 * @throws IllegalValueException if passed value is invalid.
	 */
	function __construct(float $value);

	function toValue(): float;
}