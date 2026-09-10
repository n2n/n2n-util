<?php
namespace n2n\util\col\attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ValueType {

	function __construct(public readonly string $typeName, public readonly bool $nullable = false,
			public readonly bool $convertable = false) {
	}
}