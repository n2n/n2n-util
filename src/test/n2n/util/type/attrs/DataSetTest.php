<?php
namespace n2n\util\type\attrs;

use PHPUnit\Framework\TestCase;
use n2n\util\type\mock\StringBackedEnumMock;
use n2n\util\type\mock\PureEnumMock;
use n2n\util\StringUtils;

class DataSetTest extends TestCase {
	
	function testEnum() {
		$dataSet = new DataSet(['key1' => 'value-1', 'key2' => 'CASE2']);

		$this->assertEquals(StringBackedEnumMock::VALUE1,
				$dataSet->reqEnum('key1', StringBackedEnumMock::cases()));

		$this->assertEquals(PureEnumMock::CASE2,
				$dataSet->reqEnum('key2', PureEnumMock::cases()));
	}

	/**
	 * @throws InvalidAttributeException
	 * @throws MissingAttributeFieldException
	 */
	function testReadAttributeEmpty(): void {
		$dataSet = new DataSet(['key1' => 'value-1']);

		$this->assertSame(['key1' => 'value-1'], $dataSet->readAttribute(new AttributePath([])));
	}

	private function createStringable(string $value): \Stringable {
		return new class($value) implements \Stringable{

			function __construct(private string $value) {
			}

			public function __toString(): string {
				return $this->value;
			}
		};
	}

	/**
	 * @throws InvalidAttributeException
	 */
	function testReqString(): void {
		$dataSet = new DataSet(['key1' => 'value-1', 'key2' => $this->createStringable('value-2')]);
		$this->assertSame('value-1', $dataSet->reqString('key1'));
		$this->assertSame('value-2', $dataSet->reqString('key2'));
	}

	function testOptString(): void {
		$dataSet = new DataSet(['key1' => 'value-1', 'key2' => $this->createStringable('value-2')]);
		$this->assertSame('value-1', $dataSet->optString('key1'));
		$this->assertSame('value-2', $dataSet->optString('key2'));
		$this->assertNull($dataSet->optString('key3'));
	}
}