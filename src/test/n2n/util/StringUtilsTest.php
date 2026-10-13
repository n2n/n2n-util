<?php
namespace n2n\util;

use n2n\util\type\mock\StringBackedEnumMock;
use n2n\util\test\TestObjWithToString;
use n2n\util\test\TestObjWithScalarVariables;
use InvalidArgumentException;

class StringUtilsTest extends \PHPUnit\Framework\TestCase {
	public function testConvertNonPrintables() {
		$cleanStr = ' äüöàéè+"*ç%&/ ()=?€å≈асдфCuraçao£' . "\t\r\n";
		$dirtyString = ' ​äüö‍‍‍àéè+‌"*ç%‎‏&/ ()=?€å≈асдфCuraçao£' . "\t\r\n";
		$this->assertEquals($cleanStr, StringUtils::convertNonPrintables($dirtyString));
	}

	public function testConvertNonPrintablesExpectExceptionBecauseNotScalar() {
		$this->expectException(\InvalidArgumentException::class);
		$ao = (new TestObjWithScalarVariables())->arrayObjectProperty;
		StringUtils::convertNonPrintables([$ao]);
	}

	public function testClean() {
		$cleanStr = 'äüöàéè+"*ç%&/ ()=?€å≈асдфCuraçao£';
		$cleanStrWhitespaceKept = '  äüöàéè+"*ç%&/	()=?€å≈асдфCuraçao£ ';
		$dirtyString = '  ​äüö‍‍‍àéè+‌"*ç%‎‏&/	()=?€å≈асдфCuraçao£ ';
		$this->assertEquals($cleanStr, StringUtils::clean($dirtyString, true));
		$this->assertEquals($cleanStrWhitespaceKept, StringUtils::clean($dirtyString, false));
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

	public function testContainsNonPrintables() {
		//a   Figure Space (Unicode: U+2007) char is a printable one
		$cleanStr = ' äüöàéè+"*ç%&/ ()=?€å≈асдфCuraçao£';
		$this->assertFalse(StringUtils::containsNonPrintables($cleanStr));
		//a ​ Zero Width Space (Unicode: U+200B) char for example is a non-printable
		$cleanStrSpecialWhitespace = '1äüöàéè+"*ç%&/​()=?€å≈асдфCuraçao£';
		$this->assertTrue(StringUtils::containsNonPrintables($cleanStrSpecialWhitespace));
		//there are more chars that are not allowed, there are to many to list all, and a Single made a test fail
		$dirtyString = ' äüö‍‍‍àéè+‌"*ç%‎‏&/ ()=?€å≈асдфCuraçao£ ';
		$this->assertTrue(StringUtils::containsNonPrintables($dirtyString));
		//array is searchable, it will check key and value, and it fails if either key or value is not clean
		$this->assertFalse(StringUtils::containsNonPrintables([$cleanStr => $cleanStr, $cleanStr]));
		$this->assertTrue(StringUtils::containsNonPrintables([$cleanStr => $cleanStrSpecialWhitespace, $cleanStr]));
		$this->assertTrue(StringUtils::containsNonPrintables([$cleanStrSpecialWhitespace => $cleanStr, $cleanStr]));
	}
	public function testContainsNonPrintablesExpectExceptionIfArrayContainsNinScalar() {
		//an arrayObject instead of a string or a scalar will throw an exception like it would on convert
		$this->expectException(InvalidArgumentException::class);
		$ao = (new TestObjWithScalarVariables())->arrayObjectProperty;
		$this->assertTrue(StringUtils::containsNonPrintables([$ao]));
	}
	public function testIsClean() {
		//string are ok with and without utf8-chars like äöü
		$this->assertTrue(StringUtils::isClean('asdf', true));
		$this->assertTrue(StringUtils::isClean('asdf äöü', true));

		//string with a tab as whitespace, something is clean if clean() method will do nothing on the string
		$cleanStrSpecialWhitespace = 'äüöàéèç	åасдфCuraçao';
		$this->assertFalse(StringUtils::isClean($cleanStrSpecialWhitespace, true));
		$this->assertTrue(StringUtils::isClean($cleanStrSpecialWhitespace, false));
		//emoji are allowed
		$this->assertTrue(StringUtils::isClean('£🔧👺$', false));

		//string with special chars
		$dirtyString = '  ​äüö‍‍‍àéè+‌"*ç%‎‏&/	()=?€å≈асдфCuraçao ';
		$this->assertFalse(StringUtils::isClean($dirtyString, false));
	}

	public function testIsLatin() {
		//string are ok with utf8-chars like äöü, allowed where these: Basic Latin, Latin-1 Supplement, Latin Extended-A
		$this->assertTrue(StringUtils::isLatin('asdf'));
		$this->assertTrue(StringUtils::isLatin('asdf äöü'));
		$this->assertTrue(StringUtils::isLatin('äüöàéèåçÿßœ'));
		$this->assertTrue(StringUtils::isLatin('äüöàéèçåCuraçao'));
		$this->assertTrue(StringUtils::isLatin('£$€'));

		//string with a tab as whitespace are not allowed
		$this->assertFalse(StringUtils::isLatin('äüöàéèçå	Curaçao'));
		//emoji are not allowed
		$this->assertFalse(StringUtils::isLatin('£🔧👺$'));

		//string with special chars
		$dirtyString = '  ​äüö‍‍‍àéè+‌"*ç%‎‏&/	()=?€å≈асдфCuraçao ';
		$this->assertFalse(StringUtils::isLatin($dirtyString));
	}

	public function testStripNonLatin() {
		//string are ok with utf8-chars like äöü, allowed where these: Basic Latin, Latin-1 Supplement, Latin Extended-A
		$input = 'asdf';
		$input2 = '£$€';
		$input3 = 'äüöàéèåçÿßœ';
		$input4 = 'äüöàéèçåCuraçao';
		$this->assertTrue(StringUtils::stripNonLatin($input) === $input);
		$this->assertTrue(StringUtils::stripNonLatin($input2) === $input2);
		$this->assertTrue(StringUtils::stripNonLatin($input3) === $input3);
		$this->assertTrue(StringUtils::stripNonLatin($input4) === $input4);

		$this->assertTrue(StringUtils::isLatin(StringUtils::stripNonLatin('äüöàéèçå	Curaçao')));
		$this->assertTrue(StringUtils::isLatin(StringUtils::stripNonLatin('äüöàéèçå	Curaçao', false)));
		//tab is not allowed, and either convert to space, or removed
		$this->assertEquals('äüöàéèçå Curaçao', StringUtils::stripNonLatin('äüöàéèçå	Curaçao'));
		$this->assertEquals('äüöàéèçåCuraçao', StringUtils::stripNonLatin('äüöàéèçå	Curaçao', false));
		//emoji are stripped
		$this->assertEquals('£$',StringUtils::stripNonLatin('£🔧👺$'));

		//string with special chars and a tab inside
		$dirtyString = '  ​äüö‍‍‍àéè+‌"*ç%‎‏&/	()=?€å≈асдфCuraçao ';
		//default would trim and normalize whitespaces. => no space on front and end, tab replaced by space
		$this->assertEquals('äüöàéè+"*ç%&/ ()=?€åCuraçao', StringUtils::stripNonLatin($dirtyString));
		//on false, no trim, but eliminate non-normal spaces => spaces on front and end, but no space where tab was
		$this->assertEquals(' äüöàéè+"*ç%&/()=?€åCuraçao ', StringUtils::stripNonLatin($dirtyString, false));
	}


}
