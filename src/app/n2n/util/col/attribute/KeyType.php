<?php

namespace n2n\util\col\attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class KeyType {

	function __construct(public string $typeName) {
	}
}