<?php
namespace n2n\util;

class FloatUtilsTest extends \PHPUnit\Framework\TestCase {
	public function testRemoveError() {

		$this->assertEquals(-0.44999999999999996, 1.5 - 1.95);
		$this->assertEquals(-0.45, FloatUtils::removeError(1.5 - 1.95, 2));
		$this->assertEquals(-0.4, FloatUtils::removeError(1.5 - 1.94, 1));

		$this->assertEquals(-111111103.44999999, -333333387.95 + 222222284.50);
		$this->assertEquals(-111111103.45, FloatUtils::removeError(-333333387.95 + 222222284.50, 2));

		$this->assertEquals(23.456, FloatUtils::removeError(23.456,6));
	}

}