<?php
namespace n2n\util\magic\impl;

use n2n\util\magic\impl\MagicMethodInvoker;
use PHPUnit\Framework\TestCase;

class MagicMethodInvokerTest extends TestCase {
	public function testPreservesClosureScopeAndCalledClass(): void {
		$callback = LateStaticBindingSubMock::createCallback();

		$expected = [
				'parent-private-value',
				LateStaticBindingSubMock::class,
		];

		// Establish that the original callback is correct.
		$this->assertSame($expected, $callback());

		// The invoker must not change either class context.
		$invoker = new MagicMethodInvoker();

		$this->assertSame(
				$expected,
				$invoker->invoke(null, $callback)
		);
	}
}

class LateStaticBindingBaseMock {
	private const PRIVATE_VALUE = 'parent-private-value';

	public static function createCallback(): \Closure {
		return fn(): array => [
				self::PRIVATE_VALUE,
				static::class,
		];
	}
}

class LateStaticBindingSubMock extends LateStaticBindingBaseMock {
}