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
	
	public function testPretty() {
 		$uglyString = 'Für einen Test';
 		$this->assertEquals($uglyString, StringUtils::pretty($uglyString));
		
 		$this->assertEquals('Was Ist Denn Das', StringUtils::pretty('wasIstDennDas'));
		
		$this->assertEquals('Was DIst Denn Das', StringUtils::pretty('wasDIstDennDas'));
		
		$this->assertEquals('Super Duper', StringUtils::pretty('superDuper'));
		$this->assertEquals('Super Duper Huper', StringUtils::pretty('super_duperHuper'));
		$this->assertEquals('Super 234 Dingsel DUPER Holeradio', StringUtils::pretty('super234DingselDUPER_Holeradio'));
	}
}