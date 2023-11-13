<?php

namespace n2n\util\valobj;

interface StringValueObject {

	function __construct(string $value);

	function toValue(): string;
}