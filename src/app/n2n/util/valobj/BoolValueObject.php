<?php

namespace n2n\util\valobj;

interface BoolValueObject {

	function __construct(bool $value);

	function toValue(): bool;
}