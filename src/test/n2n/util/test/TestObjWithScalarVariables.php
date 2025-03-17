<?php

namespace n2n\util\test;

class TestObjWithScalarVariables {
	public string $stringProperty;
	public int $intProperty;
	public float $floatProperty;
	public bool $boolProperty;
	public array $arrayProperty;
	public \ArrayObject $arrayObjectProperty;

	public function __construct() {
		$this->stringProperty = 'stringProperty';
		$this->intProperty = 5;
		$this->floatProperty = 3.14;
		$this->boolProperty = true;
		$this->arrayProperty = [0 => 0];
		$this->arrayObjectProperty = new \ArrayObject([0 => 0]);
	}
}