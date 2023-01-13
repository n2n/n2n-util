<?php
namespace n2n\util\magic;

use PHPUnit\Framework\TestCase;
use n2n\util\magic\impl\SimpleMagicContext;

class SimpleMagicContextTest extends TestCase {

	function testLookupAndGet() {
		$someObject = new SomeObject();
		$simpleMagicContext = new SimpleMagicContext(['some-id' => $someObject]);

		$this->assertTrue($someObject === $simpleMagicContext->lookup('some-id'));
		$this->assertTrue($someObject === $simpleMagicContext->get('some-id'));
	}

	function testHas() {
		$someObject = new SomeObject();
		$simpleMagicContext = new SimpleMagicContext(['some-id' => $someObject]);

		$this->assertTrue($simpleMagicContext->has('some-id'));
		$this->assertFalse($simpleMagicContext->has('some-other-id'));
	}

	function testLookupUnavaliable() {
		$simpleMagicContext = new SimpleMagicContext(['some-id' => new SomeObject()]);

		$this->expectException(MagicObjectUnavailableException::class);
		$simpleMagicContext->lookup('some-other-id');
	}

}

class SomeObject {

}