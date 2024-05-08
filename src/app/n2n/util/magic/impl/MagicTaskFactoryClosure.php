<?php

namespace n2n\util\magic\impl;

use n2n\util\magic\MagicTask;
use n2n\util\magic\TaskResult;
use n2n\util\magic\MagicContext;

class MagicTaskFactoryClosure implements MagicTask {

	function __construct(private \Closure $closure) {
	}

	function exec(MagicContext $magicContext): TaskResult {
	}
}