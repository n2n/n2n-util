<?php

namespace n2n\util;

use n2n\util\type\ArgUtils;

enum EnumUtils {

	private static function valEnumArg(\ReflectionEnum|string $enum) {
		if ($enum instanceof \ReflectionEnum) {
			return $enum;
		}

		try {
			return new \ReflectionEnum($enum);
		} catch (\ReflectionException $e) {
			throw new \InvalidArgumentException('Invalid enum type: ' . StringUtils::strOf($enum, true),
					previous: $e);
		}
	}

	/**
	 * @param int|string|null $backedValue
	 * @param \ReflectionEnum|string $enum
	 * @return \UnitEnum|null
	 * @throws \InvalidArgumentException if value is not associated with any case of enum
	 */
	static function backedToUnit(int|string|null $backedValue, \ReflectionEnum|string $enum): ?\UnitEnum {
		if ($backedValue === null) {
			return null;
		}

		$enum = self::valEnumArg($enum);

		$previousE = null;
		if (!$enum->isBacked()) {
			try {
				return $enum->getCase($backedValue)->getValue();
			} catch (\ReflectionException $e) {
				$previousE = $e;
			}
		} else {
			foreach ($enum->getCases() as $case) {
				if ($case->getBackingValue() == $backedValue) {
					return $case->getValue();
				}
			}
		}

		throw new \InvalidArgumentException('Value can not be associated with any case of enum '
				. $enum->getName() . ': ' . $backedValue, previous: $previousE);
	}

	static function unitToBacked(?\UnitEnum $unitEnum): int|string|null {
		if ($unitEnum === null) {
			return null;
		}

		return $unitEnum->value ?? $unitEnum->name;
	}
}
