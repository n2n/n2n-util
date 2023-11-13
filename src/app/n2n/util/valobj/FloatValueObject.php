<?php

namespace n2n\util\valobj;

interface FloatValueObject {

	function __construct(float $value);

	function toValue(): float;
}