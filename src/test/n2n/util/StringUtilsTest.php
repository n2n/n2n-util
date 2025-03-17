<?php
namespace n2n\util;

use n2n\util\type\mock\StringBackedEnumMock;
use n2n\util\test\TestObjWithToString;
use n2n\util\test\TestObjWithScalarVariables;

class StringUtilsTest extends \PHPUnit\Framework\TestCase {
	public function testConvertPrintables() {
		$cleanStr = ' äüöàéè+"*ç%&/ ()=?€å≈асдфCuraçao£' . "\t\r\n";
		$dirtyString = ' ​äüö‍‍‍àéè+‌"*ç%‎‏&/ ()=?€å≈асдфCuraçao£' . "\t\r\n";
		$this->assertEquals($cleanStr, StringUtils::convertNonPrintables($dirtyString));
	}

	public function testClean() {
		$cleanStr = 'äüöàéè+"*ç%&/ ()=?€å≈асдфCuraçao£';
		$dirtyString = '  ​äüö‍‍‍àéè+‌"*ç%‎‏&/	()=?€å≈асдфCuraçao£ ';
		$this->assertEquals($cleanStr, StringUtils::clean($dirtyString, true));
	}
	
	public function testPretty() {
 		$uglyString = 'Für einen Test';
 		$this->assertEquals($uglyString, StringUtils::pretty($uglyString));
		
 		$this->assertEquals('Was Ist Denn Das', StringUtils::pretty('wasIstDennDas'));
		
		$this->assertEquals('Was DIst Denn Das', StringUtils::pretty('wasDIstDennDas'));
		
		$this->assertEquals('Super Duper', StringUtils::pretty('superDuper'));
		$this->assertEquals('Super Duper Huper', StringUtils::pretty('super_duperHuper'));
		$this->assertEquals('Super 234 Dingsel DUPER Holeradio', StringUtils::pretty('super234DingselDUPER_Holeradio'));
	}

	public function testStrOf() {
		//null is converted to a null string = ''
		$this->assertNotNull(StringUtils::strOf(null));
		$this->assertEquals('', StringUtils::strOf(null));
		$this->assertEquals('blubb', StringUtils::strOf('blubb'));
		$this->assertEquals('value-2', StringUtils::strOf(StringBackedEnumMock::VALUE2));
		$this->assertEquals('first last', StringUtils::strOf(new TestObjWithToString('first', 'last')));
		$this->assertEquals('stringProperty', StringUtils::strOf((new TestObjWithScalarVariables())->stringProperty));
		$this->assertEquals(5, StringUtils::strOf((new TestObjWithScalarVariables())->intProperty));
		$this->assertEquals('3.14', StringUtils::strOf((new TestObjWithScalarVariables())->floatProperty));
		$this->assertEquals(true, StringUtils::strOf((new TestObjWithScalarVariables())->boolProperty));
	}

	public function testStrOfLenient() {
		//we get Type of arg instead an exception if lenient is true
		$this->assertEquals('array', StringUtils::strOf(['aaa'], true));
		$this->assertEquals('ArrayObject', StringUtils::strOf(new \ArrayObject(['aaa']), true));
		try {
			StringUtils::strOf(['aaa'], false);
			$this->fail('Expected Exception not thrown');
		} catch (\InvalidArgumentException $e) {
			$this->assertTrue(true, $e->getMessage());
		}
		try {
			StringUtils::strOf((new TestObjWithScalarVariables())->arrayProperty, false);
			$this->fail('Expected Exception not thrown');
		} catch (\InvalidArgumentException $e) {
			$this->assertTrue(true, $e->getMessage());
		}
		try {
			StringUtils::strOf((new TestObjWithScalarVariables())->arrayObjectProperty, false);
			$this->fail('Expected Exception not thrown');
		} catch (\InvalidArgumentException $e) {
			$this->assertTrue(true, $e->getMessage());
		}
	}

	/**
	 * {@link StringUtils::strOrNullOf()} is the same as {@link StringUtils::strOf()} but null stay null instead stringify it
	 */
	public function testStrOrNullOf() {
		//null stay null, else it is same as strOf
		$this->assertNull(StringUtils::strOrNullOf(null));
		$this->assertEquals('blubb', StringUtils::strOrNullOf('blubb'));
		$this->assertEquals('value-1', StringUtils::strOrNullOf(StringBackedEnumMock::VALUE1));
	}

	public function testStrOrNullOfLenient() {
		//we get Type of arg instead an exception if lenient is true
		$this->assertEquals('array', StringUtils::strOrNullOf(['aaa'], true));
		$this->assertEquals('ArrayObject', StringUtils::strOrNullOf(new \ArrayObject(['aaa']), true));
		try {
			StringUtils::strOrNullOf(['aaa'], false);
			$this->fail('Expected Exception not thrown');
		} catch (\InvalidArgumentException $e) {
			$this->assertTrue(true, $e->getMessage());
		}
	}

}
