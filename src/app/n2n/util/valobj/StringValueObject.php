<?php

namespace n2n\util\valobj;

interface StringValueObject {

	/**
	 * @param string $value
	 * @throws IllegalValueException if passed value is invalid.
	 */
	function __construct(string $value);

	function toValue(): string;
}