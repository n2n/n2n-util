<?php

namespace n2n\util\ex;

class ExUtils {

	/**
	 * Will execute the passed closure, catch all throwables and convert them to non-checked exceptions.
	 *
	 * @param \Closure $closure
	 * @return mixed
	 */
	static function try(\Closure $closure): mixed {
		return IllegalStateException::try($closure);
	}
}