<?php

namespace n2n\util;

class FloatUtils {

	/**
	 * This method can be used to remove floating point errors after calculation. For example
	 *
	 * 1.5 - 1.95 would normally result in -0.44999999999999996 but
	 * <code>FloatUtils::removeError(1.5 - 1.95, 2);</code> in the expected value -0.45
	 *
	 * @param float $value
	 * @param int $decimals how many decimals you at most expect.
	 * @return float
	 */
	static function removeError(float $value, int $decimals): float {
		return (float) number_format($value, $decimals, '.', '');
	}

}