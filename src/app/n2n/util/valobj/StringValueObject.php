<?php

namespace n2n\util\valobj;

interface StringValueObject {

	/**
	 * @param string $value
	 * @throws IncompatibleValueException if passed value is invalid.
	 */
	function __construct(string $value);

	function toValue(): string;
}