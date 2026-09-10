<?php

namespace n2n\util\magic\impl;

use n2n\util\magic\MagicTask;
use n2n\util\magic\TaskResult;
use n2n\util\magic\MagicContext;
use n2n\util\type\TypeConstraints;
use n2n\util\ex\IllegalStateException;
use n2n\util\magic\TaskInputMismatchException;

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

	function exec(?MagicContext $magicContext = null, mixed $input = null): TaskResult {
		$magicContext ??= MagicContexts::simple([]);

		$lastTaskResult = null;
		foreach ($this->steps as $key => $step) {
			if ($step instanceof MagicTask) {
				$invokeClosure = fn () => $step->exec($magicContext, $input);
			} else if ($step instanceof \Closure) {
				$invokeClosure = fn () => $this->invokeClosure($step, $magicContext, $input);
			} else {
				throw new IllegalStateException('Invalid step type: ' . get_class($step));
			}

			$lastTaskResult = $this->tryInvoke($invokeClosure, $key + 1, $invokeClosure);

			if (!$lastTaskResult->isValid()) {
				return $lastTaskResult;
			}

			$input = $lastTaskResult->get();
		}
		return $lastTaskResult ?? TaskResults::valid();
	}

	/**
	 * @throws TaskInputMismatchException
	 */
	private function tryInvoke(callable $invokeCall, int $stepNo, \Closure|MagicTask $step): TaskResult {
		try {
			return $invokeCall();
		} catch (TaskInputMismatchException $e) {
			throw new WrappedTaskInputMismatchException('PipeTask threw an TaskInputMismatchException on step#'
					. $stepNo . '. Step type: ' . get_class($step) . '. Reason: ' . $e->getMessage(), previous: $e);
		}
	}

	/**
	 * @throws TaskInputMismatchException
	 */
	private function invokeClosure(\Closure $closure, MagicContext $magicContext, mixed $input): TaskResult {
		$invoker = new MagicMethodInvoker($magicContext);
		$invoker->setClosure($closure);

		$result = $invoker->invoke(firstArgs: [$input]);

		if ($result instanceof TaskResult) {
			return $result;
		}

		if ($result instanceof MagicTask) {
			return $result->exec($magicContext, $input);
		}

		return TaskResults::valid($result);
	}
}