<?php

namespace n2n\util\magic\impl;

use n2n\util\magic\MagicTask;
use n2n\util\magic\TaskResult;
use n2n\util\magic\MagicContext;
use n2n\util\type\TypeConstraints;
use n2n\util\ex\IllegalStateException;

class ClosureTask implements MagicTask {

	function __construct(private \Closure $closure) {

	}

	function exec(?MagicContext $magicContext = null, mixed $input = null): TaskResult {
		$magicContext ??= MagicContexts::simple([]);

		$invoker = new MagicMethodInvoker($magicContext);
		$invoker->setClosure($this->closure);
		$invoker->setReturnTypeConstraint(TypeConstraints::type([TaskResult::class, MagicTask::class]));
		$result = $invoker->invoke();

		if ($result instanceof TaskResult) {
			return $result;
		}

		IllegalStateException::assertTrue($result instanceof MagicTask);
		return $result->exec($magicContext, $input);
	}
}