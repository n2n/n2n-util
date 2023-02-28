<?php

namespace n2n\util;

enum EnumUtils {

	static function isEnumType(\ReflectionEnum|\ReflectionClass|string $type): bool {
		if ($type instanceof \ReflectionClass) {
			return $type->isEnum();
		}

		return enum_exists($type);
	}

	static function isValueOfEnumType(mixed $value, \ReflectionEnum|\ReflectionClass|string $type) : bool {
		if ($value instanceof \UnitEnum) {
			return self::isUnitEnumOfType($value, $type);
		}

		if (is_string($value) || is_int($value)) {
			return self::isBackedOfUnit($value, $type);
		}

		return false;
	}

	static function valueToUnit(mixed $value, \ReflectionEnum|\ReflectionClass|string $type): \UnitEnum {
		if ($value instanceof \UnitEnum && self::isUnitEnumOfType($value, $type)) {
			return $value;
		}

		if (is_string($value) || is_int($value)) {
			return self::backedToUnit($value, $type);
		}

		throw new \InvalidArgumentException('Value can not be associated with any case of enum '
				. $type->getName() . ': ' . StringUtils::strOf($value, true));
	}

	static function isUnitEnumOfType(\UnitEnum $value, \ReflectionEnum|\ReflectionClass|string $type): bool {
		return self::valEnumArg($type)->isInstance($value);
	}

	static function valEnumArg(\ReflectionEnum|\ReflectionClass|string $enum): \ReflectionEnum {
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

	static function isBackedOfUnit(int|string|null $backedValue, \ReflectionEnum|string $enum): bool {
		try {
			self::backedToUnit($backedValue, $enum);
			return true;
		} catch (\InvalidArgumentException $e) {
			return false;
		}
	}

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
