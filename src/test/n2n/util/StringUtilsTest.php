<?php
namespace n2n\util;

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
}