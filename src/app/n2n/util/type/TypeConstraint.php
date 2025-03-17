<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\util\type;

use n2n\util\ex\IllegalStateException;

abstract class TypeConstraint implements Constraint {	
	
	/**
	 * @param mixed $value
	 * @return boolean
	 */
	abstract function isValueValid(mixed $value): bool;

	/**
	 * @return bool
	 */
	abstract function allowsNull(): bool;
	
	/**
	 * @param mixed $value
	 * @return mixed maybe converted value
	 * @throws ValueIncompatibleWithConstraintsException
	 */
	abstract function validate(mixed $value): mixed;
	
	/**
	 * @return NamedTypeConstraint[]
	 */
	abstract function getNamedTypeConstraints(): array;

	abstract function isMixed(): bool;

	abstract function isPassableBy(TypeConstraint $constraint, bool $ignoreNullAllowed = false): bool;

	abstract function isPassableTo(TypeConstraint $constraint, bool $ignoreNullAllowed = false): bool;
	
	/**
	 * @param string|\ReflectionClass|\ReflectionType|TypeConstraint $type
	 * @return TypeConstraint 
	 */
	static function create(string|\ReflectionClass|\ReflectionType|TypeConstraint $type) {
		if ($type instanceof TypeConstraint) {
			return $type;
		}
		
		if (TypeName::isUnionType($type)) {
			return UnionTypeConstraint::from($type);
		}
		
		return NamedTypeConstraint::from($type);
	}
	
	/**
	 * @param string|\ReflectionClass|null $type
	 * @param bool $allowsNull
	 * @param array $whitelistTypes
	 * @return NamedTypeConstraint
	 */
	static function createSimple(string|\ReflectionClass|null $type, bool $allowsNull = true,
			bool $convertable = false, array $whitelistTypes = array()) {
		$typeName = self::buildTypeName($type);
		
		if (TypeName::isArrayLike($typeName)) {
			return new NamedTypeConstraint($typeName, $allowsNull, TypeConstraints::mixed(true),
					$whitelistTypes, $convertable);
		}
		
		return new NamedTypeConstraint($typeName, $allowsNull, null, $whitelistTypes, $convertable);
	}

	/**
	 * @param string|\ReflectionClass|null $type
	 * @param bool $allowsNull
	 * @param string|\ReflectionClass|TypeConstraint|null $arrayFieldType
	 * @param array $whitelistTypes
	 * @param string|\ReflectionClass|TypeConstraint|null $arrayKeyType
	 * @return NamedTypeConstraint
	 */
	static function createArrayLike(
			string|\ReflectionClass|null $type, bool $allowsNull = true,
			string|\ReflectionClass|TypeConstraint|null $arrayFieldType = null,
			array $whitelistTypes = array(),
			string|\ReflectionClass|TypeConstraint|null $arrayKeyType = null): NamedTypeConstraint {
		$typeName = null;
		if ($type === null) {
			$typeName = TypeName::PSEUDO_ARRAYLIKE;
		} else {
			$typeName = self::buildTypeName($type);
		}
		
		if (!TypeName::isArrayLike($typeName)) {
			throw new \InvalidArgumentException('Type ' . $typeName . ' is not arraylike.');
		}
		
		return new NamedTypeConstraint($typeName, $allowsNull,
				($arrayFieldType === null ? TypeConstraints::mixed(true) : TypeConstraint::create($arrayFieldType)),
				$whitelistTypes, arrayKeyTypeConstraint: TypeConstraint::build($arrayKeyType));
	}
	
	
	private static function buildTypeName($type) {
		if ($type instanceof \ReflectionClass) {
			return $type->getName();
		}
		
		if ($type === null) {
			return TypeName::PSEUDO_MIXED;
		}
		
		if (!is_scalar($type)) {
			ArgUtils::valType($type, [TypeName::PSEUDO_SCALAR, \ReflectionClass::class], false, 'type');
			throw new IllegalStateException();
		}
		
		if (TypeName::isValid($type)) {
			return $type;
		}
		
		throw new \InvalidArgumentException('Type name contains invalid characters: ' . $type);
		
		// 		throw new \InvalidArgumentException(
		// 				'Invalid type parameter passed for TypeConstraint (Allowed: string, ReflectionClass): '
		// 						. TypeUtils::getTypeInfo($type));
	}
	
	/**
	 * @param string|\ReflectionClass|TypeConstraint|null $type
	 * @return \n2n\util\type\TypeConstraint
	 */
	static function build($type) {
		if ($type === null) {
			return null;
		}
		
		return self::create($type);
	}
	
	protected function createIncompatbleValueException($value, $previousE = null) {
		throw new ValueIncompatibleWithConstraintsException('Value type not allowed with constraints. Required type: '
				. $this->__toString() . '; Given type: '
				. TypeUtils::getTypeInfo($value), 0, $previousE);
	}
}
