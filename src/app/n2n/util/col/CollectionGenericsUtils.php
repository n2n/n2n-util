<?php

namespace n2n\util\col;

use n2n\util\type\NamedTypeConstraint;
use n2n\util\col\attribute\ValueType;
use n2n\util\ex\err\ConfigurationError;
use n2n\util\type\TypeName;
use n2n\util\col\attribute\KeyType;

class CollectionGenericsUtils {

	static function determineKeyTypeConstraint(TypedArray $typedArray): NamedTypeConstraint {
		$class = (new \ReflectionClass($typedArray));
		$attributes = $class->getAttributes(KeyType::class);
		if (empty($attributes)) {
			return NamedTypeConstraint::createSimple('scalar', false, true);
		}

		$attribute = $attributes[0];
		$keyType = $attribute->newInstance();
		assert($keyType instanceof KeyType);
		return self::createTypeConstraint($class, $attribute, $keyType->typeName, false,
				TypeName::isConvertable($keyType->typeName));
	}

	static function determineValueTypeConstraint(TypedArray $typedArray): NamedTypeConstraint {
		$class = (new \ReflectionClass($typedArray));
		$attributes = $class->getAttributes(ValueType::class);
		if (empty($attributes)) {
			throw new ConfigurationError('Implementations of ' . TypedArray::class
							. ' require an Attribute of type ' . ValueType::class,
					$class->getFileName(), $class->getStartLine());
		}

		$attribute = $attributes[0];
		$valueType = $attribute->newInstance();
		assert($valueType instanceof ValueType);
		return self::createTypeConstraint($class, $attribute, $valueType->typeName, $valueType->nullable,
				$valueType->convertable);
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