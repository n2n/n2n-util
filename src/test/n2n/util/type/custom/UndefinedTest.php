<?php

namespace n2n\util\type\custom;

use PHPUnit\Framework\TestCase;

class UndefinedTest extends TestCase {
	private function createTestObject(): object {
		return new class() {
			public Undefined $onlyUndefined;
			public Undefined|int $unionWithUndefined;
			public ?Undefined $nullableUndefined;
			public ?int $nullableInt;
			public int $intOnly;
			public string|int $unionNoUndefined;
		};
	}

	/**
	 * @throws \ReflectionException
	 */
	function testInitProperties(): void {
		$testObj = $this->createTestObject();

		$ref = new \ReflectionClass($testObj);
		foreach ($ref->getProperties() as $property) {
			$this->assertFalse($property->isInitialized($testObj));
		}

		Undefined::initProperties($testObj);

		$this->assertTrue($ref->getProperty('onlyUndefined')->isInitialized($testObj));
		$this->assertSame(Undefined::val(), $ref->getProperty('onlyUndefined')->getValue($testObj));

		$this->assertTrue($ref->getProperty('unionWithUndefined')->isInitialized($testObj));
		$this->assertSame(Undefined::val(), $ref->getProperty('unionWithUndefined')->getValue($testObj));

		$this->assertTrue($ref->getProperty('nullableUndefined')->isInitialized($testObj));
		$this->assertSame(Undefined::val(), $ref->getProperty('nullableUndefined')->getValue($testObj));

		$this->assertFalse($ref->getProperty('nullableInt')->isInitialized($testObj));
		$this->assertFalse($ref->getProperty('intOnly')->isInitialized($testObj));
		$this->assertFalse($ref->getProperty('unionNoUndefined')->isInitialized($testObj));
	}

	function testCoalesce(): void {
		$this->assertSame(2, Undefined::coalesce(Undefined::val(), 2, 'holeradio'));
		$this->assertSame(Undefined::val(), Undefined::coalesce(Undefined::val(), Undefined::val(), Undefined::val()));
		$this->assertSame(Undefined::val(), Undefined::coalesce(Undefined::val()));
		$this->assertSame('holeradio', Undefined::coalesce('holeradio'));
		$this->assertNull(Undefined::coalesce(null, 'holeradio'));
		$this->assertNull(Undefined::coalesce(Undefined::val(), null, 'holeradio'));
	}
}
