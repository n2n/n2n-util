<?php

namespace n2n\util\col;

use n2n\util\type\NamedTypeConstraint;
use n2n\util\col\attribute\ValueType;
use n2n\util\ex\err\ConfigurationError;
use n2n\util\type\TypeName;
use n2n\util\col\attribute\KeyType;

class CollectionTypeUtils {

	static function detectKeyTypeConstraint(\ReflectionClass $class): NamedTypeConstraint {
		$attributes = $class->getAttributes(KeyType::class);
		if (empty($attributes)) {
			return self::validateParentKeyTypeConstraint($class->getParentClass(), $class, null)
					?? NamedTypeConstraint::createSimple('scalar', false, true);
		}

		$attribute = $attributes[0];
		$keyType = $attribute->newInstance();
		assert($keyType instanceof KeyType);
		$typeConstraint = self::createTypeConstraint($class, $attribute, $keyType->typeName, false,
				TypeName::isConvertable($keyType->typeName));

		self::validateParentKeyTypeConstraint($class->getParentClass(), $class, $typeConstraint);

		return $typeConstraint;
	}

	private static function validateParentKeyTypeConstraint(?\ReflectionClass $class,
			\ReflectionClass $subClass, ?NamedTypeConstraint $subTypeConstraint): ?NamedTypeConstraint {
		if ($class === null || $class->getName() === TypedArray::class) {
			return $subTypeConstraint;
		}

		$attributes = $class->getAttributes(KeyType::class);
		if (empty($attributes)) {
			return self::validateParentKeyTypeConstraint($class->getParentClass(), $subClass, $subTypeConstraint);
		}

		$attribute = $attributes[0];
		$valueType = $attribute->newInstance();
		assert($valueType instanceof KeyType);
		$typeConstraint = self::createTypeConstraint($class, $attribute, $valueType->typeName, false,
				true);
		if ($subTypeConstraint !== null) {
			self::validateTypeConstraintCompatibility(KeyType::class, $subClass, $subTypeConstraint,
					$class, $typeConstraint);
		}

		return self::validateParentKeyTypeConstraint($class->getParentClass(), $class, $typeConstraint);
	}

	static function detectValueTypeConstraint(\ReflectionClass $class): NamedTypeConstraint {
		if (!$class->isSubclassOf(TypedArray::class)) {
			throw new \InvalidArgumentException('Passed class must be a sub class of ' . TypedArray::class
					. ': ' . $class->getName());
		}

		$attributes = $class->getAttributes(ValueType::class);

		if (empty($attributes)) {
			throw new ConfigurationError('Implementations of ' . TypedArray::class
					. ' require an Attribute of type ' . ValueType::class,
					$class->getFileName(), $class->getStartLine());
		}

		$attribute = $attributes[0];
		$valueType = $attribute->newInstance();
		assert($valueType instanceof ValueType);
		$typeConstraint = self::createTypeConstraint($class, $attribute, $valueType->typeName, $valueType->nullable,
				$valueType->convertable);

		if (null !== ($parentClass = $class->getParentClass())) {
			self::validateParentValueTypeConstraint($parentClass, $class, $typeConstraint);
		}

		return $typeConstraint;
	}

	private static function validateParentValueTypeConstraint(?\ReflectionClass $class,
			\ReflectionClass $subClass, NamedTypeConstraint $subTypeConstraint): void {
		if ($class === null || $class->getName() === TypedArray::class) {
			return;
		}

		$attributes = $class->getAttributes(ValueType::class);
		if (empty($attributes)) {
			self::validateParentValueTypeConstraint($class->getParentClass(), $subClass, $subTypeConstraint);
			return;
		}

		$attribute = $attributes[0];
		$valueType = $attribute->newInstance();
		assert($valueType instanceof ValueType);
		$typeConstraint = self::createTypeConstraint($class, $attribute, $valueType->typeName, $valueType->nullable,
				$valueType->convertable);
		self::validateTypeConstraintCompatibility(ValueType::class, $subClass, $subTypeConstraint,
				$class, $typeConstraint);

		self::validateParentValueTypeConstraint($class->getParentClass(), $class, $typeConstraint);
	}

	private static function validateTypeConstraintCompatibility(string $attributeName,
			\ReflectionClass $class, NamedTypeConstraint $typeConstraint,
			\ReflectionClass $parentClass, NamedTypeConstraint $parentTypeConstraint): void {
		if (!$typeConstraint->isPassableTo($parentTypeConstraint)) {
			throw new ConfigurationError($attributeName . ' attribute of ' . $class->getName()
					. ' conflicts with the ' . $attributeName . ' attribute of ' . $parentClass->getName(),
					$class->getFileName(), $class->getStartLine());
		}
	}


	private static function createTypeConstraint(\ReflectionClass $class, \ReflectionAttribute $attribute,
			string $typeName, bool $nullable, bool $convertable): NamedTypeConstraint {
		if (!TypeName::isValid($typeName)) {
			throw new ConfigurationError('Invalid type "' . $typeName . '" defined in '
					. $attribute->name, $class->getFileName(), $class->getStartLine());
		}

		return NamedTypeConstraint::createSimple($typeName, $nullable, $convertable);
	}


}