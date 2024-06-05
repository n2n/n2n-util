<?php

namespace n2n\util\magic\impl;

use PHPUnit\Framework\TestCase;
use n2n\util\magic\MagicContext;
use n2n\util\magic\MagicTaskExecutionException;
use n2n\util\magic\MagicTask;
use n2n\util\magic\MagicArray;
use n2n\util\magic\TaskResult;

class ClosureTaskTest extends TestCase {

	/**
	 * @throws MagicTaskExecutionException
	 */
	function testClosureWithMagicTasks(): void {
		$magicTask1 = $this->createMock(MagicTask::class);
		$magicTask1->expects($this->once())->method('exec')->willReturn(TaskResults::valid());

		$this->assertTrue(MagicTasks::closure(fn () => $magicTask1)
				->exec($this->createMock(MagicContext::class))->isValid());

		$magicTask2 = $this->createMock(MagicTask::class);
		$magicTask2->expects($this->once())
				->method('exec')
				->willReturn(TaskResults::invalid($this->createMock(MagicArray::class)));

		$this->assertFalse(MagicTasks::closure(fn () => $magicTask2)
				->exec($this->createMock(MagicContext::class))->isValid());
	}

	/**
	 * @throws MagicTaskExecutionException
	 */
	function testClosureWithTaskResults(): void {
		$this->assertTrue(MagicTasks::closure(fn () => TaskResults::valid())
				->exec($this->createMock(MagicContext::class))->isValid());

		$this->assertFalse(MagicTasks
				::closure(fn () => TaskResults::invalid($this->createMock(MagicArray::class)))
				->exec($this->createMock(MagicContext::class))->isValid());
	}
}