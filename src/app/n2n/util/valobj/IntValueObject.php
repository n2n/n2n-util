<?php

namespace n2n\util\valobj;

interface IntValueObject {

	function __construct(int $value);

	function toValue(): int;
}