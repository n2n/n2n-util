<?php
namespace n2n\util\type\attrs;

use PHPUnit\Framework\TestCase;

class DataMapTest extends TestCase {
	
	function testSet() {
		$dataMap = new DataMap([ 'key1' => ['skey1' => 'string']]);
		
		$dataMap->set('key1/skey1/sskey1', 'huii');
		
		$array = $dataMap->toArray();
		
		$this->assertTrue($array['key1']['skey1']['sskey1'] === 'huii');
	}
}