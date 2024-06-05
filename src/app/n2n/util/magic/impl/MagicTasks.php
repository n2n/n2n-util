<?php

namespace n2n\util\magic\impl;

use n2n\util\magic\MagicTask;

class MagicTasks {

	static function pipe(\Closure|MagicTask ...$steps): PipeTask {
		return new PipeTask($steps);
	}

	static function closure(\Closure $closure): ClosureTask {
		return new ClosureTask($closure);
	}
}