<?php

namespace n2n\util\magic\impl;

class MagicTasks {

	static function factory(\Closure $closure): MagicTaskFactoryClosure {
		return new MagicTaskFactoryClosure($closure);
	}
}