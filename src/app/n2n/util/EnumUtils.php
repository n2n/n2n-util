<?php

namespace n2n\util;

use n2n\util\col\ArrayUtils;
use n2n\util\type\attrs\InvalidAttributeException;
use n2n\util\type\TypeUtils;

enum EnumUtils {

	static function isValueOfPseudoUnit(mixed $value, array|\ReflectionEnum|\ReflectionClass|string $allowedValues): bool {
		try {
			self::valueToPseudoUnit($value, $allowedValues);
			return true;
		} catch (\InvalidArgumentException $e) {
			return false;
		}
	}

	/**
	 * @param mixed $value
	 * @param array $allowedValues
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	static function valueToPseudoUnit(mixed $value, array|\ReflectionEnum|\ReflectionClass|string $allowedValues): mixed {
		if (!is_array($allowedValues)) {
			return self::valueToUnit($value, $allowedValues);
		}

		$valueMap = [];

		foreach ($allowedValues as $key => $allowedValue) {
			if ($value === $allowedValue) {
				return $value;
			}

			if (!($allowedValue instanceof \UnitEnum)) {
				continue;
			}

			$backedValue = self::unitToBacked($allowedValue);
			$allowedValues[$key] = $backedValue;
			$valueMap[$backedValue] = $allowedValue;
		}

		if (!ArrayUtils::inArrayLike($value, $allowedValues)) {
			throw new \InvalidArgumentException('Value must be equal to one of following values: '
					. implode(', ', array_map(fn ($v) => StringUtils::strOf($v, true), $allowedValues))
					. '. Given: ' . TypeUtils::buildScalar($value));
		}

		return $valueMap[$value] ?? $value;
	}

	static function isEnumType(\ReflectionEnum|\ReflectionClass|string $type): bool {
		if ($type instanceof \ReflectionClass) {
			return $type->isEnum();
		}

		return enum_exists($type);
	}

	static function isValueOfEnumType(mixed $value, \ReflectionEnum|\ReflectionClass|string $type): bool {
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

	/**
	 * @template T
	 * @param int|string|null $backedValue
	 * @param \ReflectionEnum|class-string<T> $enum
	 * @return T|mixed
	 */
	static function backedToUnit(int|string|null $backedValue, \ReflectionEnum|string $enum): mixed {
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

	/**
	 * @param \ReflectionEnum $enum
	 * @return \UnitEnum[]
	 */
	static function units(\ReflectionEnum $enum): array {
		return array_map(fn($c) => $c->getValue(),  $enum->getCases());
	}

	static function unitToName(\UnitEnum $unitEnum): string {
		return $unitEnum->name;
	}

	static function nameToUnit(string $name, \ReflectionEnum $enum): \UnitEnum {
		try {
			return $enum->getCase($name)->getValue();
		} catch (\ReflectionException $e) {
			throw new \InvalidArgumentException('Name not part of enum: ' . $name, previous: $e);
		}
	}

	static function extractEnumTypeName(\ReflectionUnionType|\ReflectionNamedType|null $type): ?string {
		if ($type === null) {
			return null;
		}

		if ($type instanceof \ReflectionUnionType) {
			$namedTypes = $type->getTypes();
		} else {
			$namedTypes = [$type];
		}

		foreach ($namedTypes as $namedType) {
			if ($namedType->isBuiltin() || !self::isEnumType($namedType)) {
				continue;
			}

			return $namedType->getName();
		}

		return null;
	}
}
