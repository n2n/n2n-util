<?php

namespace n2n\util\magic\impl;

use PHPUnit\Framework\TestCase;
use n2n\util\magic\MagicContext;
use n2n\util\magic\MagicTaskExecutionException;
use n2n\util\magic\MagicTask;
use n2n\util\magic\MagicArray;
use n2n\util\magic\TaskResult;

class PipeTaskTest extends TestCase {

	/**
	 * @throws MagicTaskExecutionException
	 */
	function testEmptyPipe(): void {
		$this->assertTrue(MagicTasks::pipe()->exec($this->createMock(MagicContext::class))->isValid());
	}

	/**
	 * @throws MagicTaskExecutionException
	 */
	function testPipeWithMagicTasks(): void {
		$magicTask1 = $this->createMock(MagicTask::class);
		$magicTask1->expects($this->once())->method('exec')->willReturn(TaskResults::valid());

		$magicTask2 = $this->createMock(MagicTask::class);
		$magicTask2->expects($this->once())
				->method('exec')
				->willReturn(TaskResults::invalid($this->createMock(MagicArray::class)));

		$magicTask3 = $this->createMock(MagicTask::class);
		$magicTask3->expects($this->never())->method('exec');


		$this->assertFalse(MagicTasks::pipe($magicTask1, $magicTask2, $magicTask3)
				->exec($this->createMock(MagicContext::class))->isValid());
	}

	/**
	 * @throws MagicTaskExecutionException
	 */
	function testPipeWithClosuredMagicTasks(): void {
		$magicTask1 = $this->createMock(MagicTask::class);
		$magicTask1->expects($this->once())->method('exec')->willReturn(TaskResults::valid('initial-holeradio'));

		$magicTask2 = $this->createMock(MagicTask::class);
		$magicTask2->expects($this->once())
				->method('exec')
				->willReturn(TaskResults::invalid($this->createMock(MagicArray::class)));

		$magicTask3 = $this->createMock(MagicTask::class);
		$magicTask3->expects($this->never())->method('exec');


		$this->assertFalse(
				MagicTasks::pipe(
						fn () => $magicTask1,
						function (string $arg) {
							$this->assertEquals('initial-holeradio', $arg);
							return 'holeradio';
						},
						function (string $arg) use ($magicTask2) {
							$this->assertEquals('holeradio', $arg);
							return $magicTask2;
						},
						fn () => $magicTask3)
				->exec($this->createMock(MagicContext::class))->isValid());
	}
}