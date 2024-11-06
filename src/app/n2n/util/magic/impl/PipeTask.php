<?php

namespace n2n\util\magic\impl;

use n2n\util\magic\MagicTask;
use n2n\util\magic\TaskResult;
use n2n\util\magic\MagicContext;
use n2n\util\type\TypeConstraints;
use n2n\util\ex\IllegalStateException;

class PipeTask implements MagicTask {

	/**
	 * @param array<\Closure|MagicTask> $steps
	 */
	function __construct(private array $steps = []) {

	}

	function addStep(\Closure|MagicTask $step): static {
		$this->steps[] = $step;
		return $this;
	}

	function exec(MagicContext $magicContext = null, mixed $input = null): TaskResult {
		$magicContext ??= MagicContexts::simple([]);

		$lastTaskResult = null;
		foreach ($this->steps as $step) {
			if ($step instanceof MagicTask) {
				$lastTaskResult = $step->exec($magicContext, $input);
			} else if ($step instanceof \Closure) {
				$lastTaskResult = $this->invokeClosure($step, $magicContext, $lastTaskResult, $input);
			} else {
				throw new IllegalStateException('Invalid step type: ' . get_class($step));
			}

			if (!$lastTaskResult->isValid()) {
				return $lastTaskResult;
			}

			$input = $lastTaskResult->get();
		}
		return $lastTaskResult ?? TaskResults::valid();
	}

	private function invokeClosure(\Closure $closure, MagicContext $magicContext, ?TaskResult $lastTaskResult,
			mixed $input): TaskResult {
		$invoker = new MagicMethodInvoker($magicContext);
		$invoker->setClosure($closure);
		$invoker->setReturnTypeConstraint(TypeConstraints::type([TaskResult::class, MagicTask::class]));

		$firstArgs = [];
		if ($lastTaskResult !== null) {
			$firstArgs[] = $lastTaskResult;
		}
		$result = $invoker->invoke(firstArgs: $firstArgs);

		if ($result instanceof TaskResult) {
			return $result;
		}

		return $result->exec($magicContext, $input);
	}
}