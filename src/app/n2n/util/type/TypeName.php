<?php
namespace n2n\util\type;

use n2n\util\StringUtils;
use n2n\util\io\IoUtils;
use n2n\util\EnumUtils;

class TypeName {
	const NULL = 'null';
	const STRING = 'string';
	const INT = 'int';
	const FLOAT = 'float';
	const BOOL = 'bool';
	const ARRAY = 'array';
	const RESOURCE = 'resource';
	const OBJECT = 'object';
	
	const PSEUDO_SCALAR = 'scalar';
	const PSEUDO_MIXED = 'mixed';
	const PSEUDO_ARRAYLIKE = 'arraylike';
	const PSEUDO_NUMERIC = 'numeric';
	
	/**
	 * @param string $typeName
	 * @return boolean
	 */
	static function isScalar(string $typeName) {
		switch ($typeName) {
			case self::STRING:
			case self::INT:
			case self::FLOAT:
			case self::BOOL:
			case self::PSEUDO_SCALAR:
			case self::PSEUDO_NUMERIC:
				return true;
			default:
				return false;
		}
	}
	
	/**
	 * @param mixed $value
	 * @param string $typeName
	 */
	static function convertValue($value, string $typeName) {
		switch ($typeName) {
			case self::STRING;
				if (is_scalar($value)) {
					return (string) $value;
				}
				
				throw self::createValueNotConvertableException($value, $typeName);
			case self::BOOL:
				return (bool) $value;
			case self::FLOAT:
				if (is_numeric($value)) {
					return (float) $value;
				}
				
				throw self::createValueNotConvertableException($value, $typeName);
			case self::INT:
				if (is_numeric($value) && (int) $value == $value) {
					return (int) $value;
				}
				
				throw self::createValueNotConvertableException($value, $typeName);
				
			default:
				if (EnumUtils::isEnumType($typeName)) {
					return EnumUtils::valueToUnit($value, $typeName);
				}

				throw new \InvalidArgumentException('It is not possible to convert a value to ' . $typeName);
		}
	}
	
	/**
	 * @param mixed $value
	 * @param string $typeName
	 * @return bool
	 */
	static function isValueConvertTo($value, string $typeName) {
		switch ($typeName) {
			case self::STRING;
				return is_scalar($value);
			case self::BOOL:
				return true;
			case self::FLOAT:
				return is_numeric($value);
			case self::INT:
				return is_numeric($value) && ((int) $value == $value);
			default:
				if (EnumUtils::isEnumType($typeName)) {
					return EnumUtils::isValueOfEnumType($value, $typeName);
				}

				return false;
		}
	}
	
	/**
	 * @param string $typeName
	 * @return bool
	 */
	static function isConvertable(string $typeName) {
		switch ($typeName) {
			case self::STRING:
			case self::BOOL:
			case self::INT:
			case self::FLOAT:
				return true;
			default:
				return EnumUtils::isEnumType($typeName);
		}
	}
	
	/**
	 * @param mixed $value
	 * @param string $typeName
	 * @throws \InvalidArgumentException
	 */
	private static function createValueNotConvertableException($value, string $typeName) {
		throw new \InvalidArgumentException('Value ' . TypeUtils::getTypeInfo($value) . ' is not convertable to ' . $typeName);
	}
	
	/**
	 * @param string $testingTypeName
	 * @param string $typeName
	 * @return boolean
	 */
	static function isA(string $testingTypeName, string $typeName): bool {
		if ($testingTypeName == $typeName || $typeName == self::PSEUDO_MIXED) {
			return true;
		}
		
		switch ($testingTypeName) {
			case self::INT:
			case self::FLOAT:
				return $typeName == self::INT || $typeName == self::PSEUDO_NUMERIC || $typeName == self::PSEUDO_SCALAR;
			case self::STRING:
			case self::BOOL:
			case self::PSEUDO_NUMERIC:
				return $typeName == self::PSEUDO_SCALAR;
			case self::ARRAY:
				return $typeName == self::PSEUDO_ARRAYLIKE;
			case self::OBJECT:
			case self::RESOURCE:
			case self::PSEUDO_MIXED:
			case self::PSEUDO_SCALAR:
				return false;
		}
		
		if ($typeName == self::PSEUDO_ARRAYLIKE) {
			return self::isArrayLike($testingTypeName);
		}

		if ($typeName === self::OBJECT) {
			return class_exists($testingTypeName);
		}
		
		return is_subclass_of($testingTypeName, $typeName);
	}

	static function isValueA(mixed $value, string $typeName): bool {
		switch ($typeName) {
			case TypeName::PSEUDO_MIXED:
				return true;
			case TypeName::PSEUDO_SCALAR:
				return is_scalar($value);
			case TypeName::ARRAY:
				return is_array($value);
			case TypeName::STRING:
				return is_string($value);
			case TypeName::PSEUDO_NUMERIC:
				return is_numeric($value);
			case TypeName::INT:
				return is_int($value);
			case TypeName::FLOAT:
				return is_float($value);
			case TypeName::BOOL:
				return is_bool($value);
			case TypeName::OBJECT:
				return is_object($value);
			case TypeName::RESOURCE:
				return is_resource($value);
			case TypeName::PSEUDO_ARRAYLIKE:
				return self::isValueArrayLike($value);
			case TypeName::NULL:
			case 'NULL':
				return $value === null;
			default:
				return is_a($value, $typeName);
		}
	}
	
	/**
	 * @param mixed $value
	 * @return boolean
	 */
	static function isValueArrayLike($value) {
		return is_array($value) || ($value instanceof \ArrayAccess
				&& $value instanceof \IteratorAggregate && $value instanceof \Countable);
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return boolean
	 */
	static function isClassArrayLike(\ReflectionClass $class) {
		return $class->implementsInterface('ArrayAccess')
				&& $class->implementsInterface('IteratorAggregate')
				&& $class->implementsInterface('Countable');
	}
	
	/**
	 * @param string $typeName
	 * @return boolean
	 */
	static function isArrayLike(string $typeName) {
		switch ($typeName) {
			case self::ARRAY:
			case self::PSEUDO_ARRAYLIKE:
			case 'ArrayObject':
				return true;
			case self::STRING:
			case self::INT:
			case self::FLOAT:
			case self::BOOL:
			case self::RESOURCE:
			case self::OBJECT:
			case self::NULL:
			case self::PSEUDO_SCALAR:
			case self::PSEUDO_MIXED:
			case self::PSEUDO_NUMERIC:
				return false;
		}
		
		return is_subclass_of($typeName, 'ArrayAccess')
				&& is_subclass_of($typeName, 'IteratorAggregate')
				&& is_subclass_of($typeName, 'Countable');
	}
	
	/**
	 * @param string $typeName
	 */
	static function isNullable(string $typeName) {
		switch ($typeName) {
			case self::PSEUDO_MIXED:
			case self::NULL:
				return true;
			default:
				return false;
		}
	}
	
	/**
	 * @param string $typeName
	 * @return boolean
	 */
	static function isSafe(string $typeName): bool {
		return match ($typeName) {
			self::PSEUDO_MIXED, self::PSEUDO_SCALAR, self::PSEUDO_NUMERIC, self::PSEUDO_ARRAYLIKE => false,
			default => true,
		};
	}
	
	/**
	 * @param string $typeName
	 * @return bool
	 */
	static function isValid(string $typeName): bool {
		return (bool) preg_match('/[0-9a-zA-Z_\\\\]/', $typeName);
	}
	
	const UNION_TYPE_SEPARATOR = '|';
	const INTERSECTION_TYPE_SEPARATOR = '&';

	const NULLABLE_PREFIX = '?';

	static function isUnionType(string|\ReflectionType $type) {
		if (is_string($type)) {
			return StringUtils::contains(self::UNION_TYPE_SEPARATOR, $type);
		}
		
		return ($type instanceof \ReflectionUnionType);
	}

	static function isIntersectionType(string|\ReflectionType $type): bool {
		if (!is_string($type)) {
			return ($type instanceof \ReflectionUnionType);
		}

		if (!str_contains($type, self::INTERSECTION_TYPE_SEPARATOR)) {
			return false;
		}

		$trimedType = trim(trim($type), '()');
		return !str_contains($trimedType, '(') && !str_contains($trimedType, ')');
	}

	static function isNamedType(string|\ReflectionType $type) {
		if (is_string($type)) {
			return !StringUtils::contains(self::UNION_TYPE_SEPARATOR, $type)
					&& !StringUtils::contains(self::INTERSECTION_TYPE_SEPARATOR, $type);
		}

		return $type instanceof \ReflectionNamedType;
	}

	/**
	 * @param string|\ReflectionNamedType $type
	 * @return string[]
	 */
	static function extractNamedTypeNames(string|\ReflectionNamedType $type): array {
		if ($type instanceof \ReflectionNamedType) {
			if ($type->allowsNull()) {
				return [$type->getName(), self::NULL];
			}

			return [$type->getName()];
		}

		if (StringUtils::startsWith(self::NULLABLE_PREFIX, $type)) {
			return [mb_substr($type, 0, mb_strlen(self::NULLABLE_PREFIX), $type), self::NULL];
		}

		return [$type];
	}

	static function extractUnionTypeNames(string|\ReflectionUnionType $type) {
		if ($type instanceof \ReflectionUnionType) {
			return array_map(function ($namedType) { return $namedType->getName(); }, $type->getTypes());
		}
		
		return array_map(
				function ($typeName) use ($type) {
					$typeName = trim($typeName);
					
					if (empty($typeName) || IoUtils::hasSpecialChars($typeName)) {
						throw new \InvalidArgumentException('Invalid union type: ' . $type);
					}
					
					return $typeName;
				}, explode(self::UNION_TYPE_SEPARATOR, $type));
	}
	
	static function concatUnionTypeNames(array $typeNames) {
		ArgUtils::valArray($typeNames, 'string');
		return implode(self::UNION_TYPE_SEPARATOR, $typeNames);
	}

	static function extractIntersectionTypeNames(string|\ReflectionIntersectionType $type) {
		if ($type instanceof \ReflectionIntersectionType) {
			return array_map(function ($namedType) { return $namedType->getName(); }, $type->getTypes());
		}

		return array_map(
				function ($typeName) use ($type) {
					$typeName = trim($typeName);

					if (empty($typeName) || IoUtils::hasSpecialChars($typeName)) {
						throw new \InvalidArgumentException('Invalid union type: ' . $type);
					}

					return $typeName;
				}, explode(self::INTERSECTION_TYPE_SEPARATOR, trim(trim($type), '()')));
	}
}
