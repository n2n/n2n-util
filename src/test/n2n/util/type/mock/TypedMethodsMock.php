<?php
namespace n2n\util\type\mock;

class TypedMethodsMock {
	
	function intParam(int $arg) {	
	}
	
	function intStringParam(int|string $arg) {
	}
	
	function stringIntParam(string|int $arg) {
	}
}
