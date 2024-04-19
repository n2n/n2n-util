<?php

namespace n2n\util\ex;

use PHPUnit\Framework\TestCase;
use n2n\util\ex\err\impl\WarningError;

class ExUtilsTest extends TestCase {

	function testConvertTriggeredErrors() {
		$this->expectException(WarningError::class);

		$errorHandlerCalled = false;
		set_error_handler(function($errno, $errstr) use (&$errorHandlerCalled) {
			$errorHandlerCalled = true;
			$this->assertEquals(E_USER_WARNING, $errno);
			$this->assertEquals('hui', $errstr);
			$this->assertEquals(0, E_USER_WARNING & error_reporting());
			$this->assertEquals(0, error_reporting());
			return false;
		});

		try {
			ExUtils::convertTriggeredErrors(function() {
				trigger_error('hui', E_USER_WARNING);
			});
		} finally {
			restore_error_handler();
		}

		$this->assertTrue($errorHandlerCalled);
	}

	function testConvertTriggeredErrorsErrorLevelTrigger() {
		$errorHandlerCalled = false;
		set_error_handler(function($errno, $errstr) use (&$errorHandlerCalled) {
			$errorHandlerCalled = true;
			$this->assertEquals(E_USER_WARNING, $errno);
			$this->assertEquals('hui', $errstr);
			$this->assertEquals(E_USER_WARNING, E_USER_WARNING & error_reporting());
			$this->assertEquals(E_ALL ^ E_USER_ERROR, error_reporting());
			return false;
		});

		try {
			ExUtils::convertTriggeredErrors(function() {
				trigger_error('hui', E_USER_WARNING);
			}, E_USER_ERROR);
		} finally {
			restore_error_handler();
		}

		$this->assertTrue($errorHandlerCalled);
	}

	function testConvertTriggeredErrorsErrorLevelThrow() {
		$this->expectException(WarningError::class);

		$errorHandlerCalled = false;
		set_error_handler(function($errno, $errstr) use (&$errorHandlerCalled) {
			$errorHandlerCalled = true;
			$this->assertEquals(E_USER_WARNING, $errno);
			$this->assertEquals('hui', $errstr);
			$this->assertEquals(0, E_USER_WARNING & error_reporting());
			$this->assertEquals(E_ALL ^ E_USER_WARNING, error_reporting());
			return false;
		});

		try {
			ExUtils::convertTriggeredErrors(function() {
				trigger_error('hui', E_USER_WARNING);
			}, E_USER_WARNING);
		} finally {
			restore_error_handler();
		}

		$this->assertTrue($errorHandlerCalled);
	}
}