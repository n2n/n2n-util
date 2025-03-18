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
}