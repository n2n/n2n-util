<?php

namespace n2n\util;

use PHPUnit\Framework\TestCase;
use n2n\util\type\mock\StringBackedEnumMock;
use n2n\util\type\mock\PureEnumMock;

class EnumUtilsTest extends TestCase {


	function testUnitToBacked() {
		$this->assertEquals(StringBackedEnumMock::VALUE2->value,
				EnumUtils::unitToBacked(StringBackedEnumMock::VALUE2));

		$this->assertEquals('CASE1',
				EnumUtils::unitToBacked(PureEnumMock::CASE1));

		$this->assertEquals(null, EnumUtils::unitToBacked(null));
	}

	function testBackedToUnit() {
		$this->assertEquals(StringBackedEnumMock::VALUE2,
				EnumUtils::backedToUnit(StringBackedEnumMock::VALUE2->value, StringBackedEnumMock::class));

		$this->assertEquals(PureEnumMock::CASE1,
				EnumUtils::backedToUnit('CASE1', PureEnumMock::class));
	}

	function testBackedToUnitBackedFail() {
		$this->expectException(\InvalidArgumentException::class);

		EnumUtils::backedToUnit(StringBackedEnumMock::VALUE2->value, PureEnumMock::class);
	}

	function testBackedToUnitPureFail() {
		$this->expectException(\InvalidArgumentException::class);

		EnumUtils::backedToUnit('CASE1', StringBackedEnumMock::class);
	}


	function testValueToPseudoBackedEnum() {
		$this->assertEquals(StringBackedEnumMock::VALUE1,
				EnumUtils::valueToPseudoUnit('value-1', StringBackedEnumMock::cases()));
		$this->assertEquals(StringBackedEnumMock::VALUE1,
				EnumUtils::valueToPseudoUnit(StringBackedEnumMock::VALUE1, StringBackedEnumMock::cases()));

		$this->assertEquals(StringBackedEnumMock::VALUE1,
				EnumUtils::valueToPseudoUnit('value-1', StringBackedEnumMock::class));
		$this->assertEquals(StringBackedEnumMock::VALUE1,
				EnumUtils::valueToPseudoUnit(StringBackedEnumMock::VALUE1, StringBackedEnumMock::class));

		$this->expectException(\InvalidArgumentException::class);
		EnumUtils::valueToPseudoUnit('VALUE1', StringBackedEnumMock::cases());

	}
	function testToPseudoEnum() {
		$this->assertEquals('v1', EnumUtils::valueToPseudoUnit('v1', ['v1', 'v2']));

		$this->expectException(\InvalidArgumentException::class);
		EnumUtils::valueToPseudoUnit('v3', ['v1', 'v2']);
	}

	function testToPseudoPureEnum() {
		$this->assertEquals(PureEnumMock::CASE1,
				EnumUtils::valueToPseudoUnit('CASE1', PureEnumMock::cases()));
		$this->assertEquals(PureEnumMock::CASE1,
				EnumUtils::valueToPseudoUnit(PureEnumMock::CASE1, PureEnumMock::cases()));

		$this->assertEquals(PureEnumMock::CASE1,
				EnumUtils::valueToPseudoUnit('CASE1', PureEnumMock::class));
		$this->assertEquals(PureEnumMock::CASE1,
				EnumUtils::valueToPseudoUnit(PureEnumMock::CASE1, PureEnumMock::class));

		$this->expectException(\InvalidArgumentException::class);
		EnumUtils::valueToPseudoUnit('CASE3', PureEnumMock::cases());

	}

	function testUnits(): void {
		$this->assertEquals(StringBackedEnumMock::cases(),
				EnumUtils::units(new \ReflectionEnum(StringBackedEnumMock::class)));
		$this->assertEquals(PureEnumMock::cases(),
				EnumUtils::units(new \ReflectionEnum(PureEnumMock::class)));
	}

	function testNameToUnit(): void {
		$this->assertEquals(StringBackedEnumMock::VALUE1, EnumUtils::nameToUnit('VALUE1',
				new \ReflectionEnum(StringBackedEnumMock::class)));
	}

	function testUnitToName(): void {
		$this->assertEquals('VALUE1', EnumUtils::unitToName(StringBackedEnumMock::VALUE1));
	}
}
