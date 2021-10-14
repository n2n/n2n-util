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

use n2n\reflection\ReflectionUtils;
use n2n\util\col\ArrayUtils;
use n2n\util\ex\IllegalStateException;

class UnionTypeConstraint implements Constraint {	
	private $typeConstraints = [];

	/**
	 * @param TypeConstraint[] $typeConstraints
	 * @param array $whitelistTypes
	 */
	protected function __construct(array $typeConstraints = [], array $whitelistTypes = array()) {
		ArgUtils::valArray($typeConstraints, TypeConstraint::class);
		$this->typeConstraints = $typeConstraints;
		$this->whitelistTypes = $whitelistTypes;
	}
	
	function setWhitelistTypes(array $whitelistTypes) {
		$this->whitelistTypes = $whitelistTypes;
		return $this;
	}
	
	function getWhitelistTypes() {
		return $this->whitelistTypes;
	}
	
	/**
	 * @return TypeConstraint[]
	 */
	function getTypeConstraints() {
		return $this->typeConstraints;
	}
	
	function isValueValid($value): bool {
		foreach ($this->whitelistTypes as $whitelistType) {
			if (TypeUtils::isValueA($value, $whitelistType, false)) return true;
		}
		
		foreach ($this->typeConstraints as $typeConstraint) {
			if ($typeConstraint->isValueValid($value)) {
				return true;
			}
		}
		
		return false;
	}
	
	function validate($value) {
		foreach ($this->whitelistTypes as $whitelistType) {
			if (TypeUtils::isValueA($value, $whitelistType, false)) {
				return $value;
			}
		}
		
		if ($value === null) {
			if ($this->allowsNull()) return $value;
			
			throw new ValueIncompatibleWithConstraintsException(
					'Null not allowed with constraints.');
		}
		
		if (!TypeUtils::isValueA($value, $this->typeName, false)) {
			if (!$this->convertable) {
				throw $this->createIncompatbleValueException($value);
			}
			
			try {
				$value = TypeName::convertValue($value, $this->typeName);
			} catch (\InvalidArgumentException $e) {
				throw $this->createIncompatbleValueException($value, $e);
			}
		}
		
		if ($this->arrayFieldTypeConstraint === null) {
			return $value;
		}
		
		if (!ArrayUtils::isArrayLike($value)) {
			if ($this->typeName === null) {
				throw $this->createIncompatbleValueException($value);
			}
			
			throw new IllegalStateException('Illegal constraint ' . $this->__toString() . ' defined:'
					. $this->typeName . ' is no ArrayType.');
		}
		
		foreach ($value as $key => $fieldValue) {
			try {
				$value[$key] = $this->arrayFieldTypeConstraint->validate($fieldValue);
			} catch (ValueIncompatibleWithConstraintsException $e) {
				throw new ValueIncompatibleWithConstraintsException(
						'Value type not allowed with constraints '
						. $this->__toString() . '. Array field (key: \'' . $key . '\') contains invalid value.', null, $e);
			}
		}
		
		return $value;
	}
	
	private function createIncompatbleValueException($value, $previousE = null) {
		throw new ValueIncompatibleWithConstraintsException(
				'Value type not allowed with constraints. Required type: '
				. $this->__toString() . '; Given type: '
				. TypeUtils::getTypeInfo($value), null, $previousE);
	}
	
	function isEmpty() {
		return $this->typeName === TypeName::PSEUDO_MIXED && $this->allowsNull 
				&& ($this->arrayFieldTypeConstraint === null || $this->arrayFieldTypeConstraint->isEmpty());
	}
	/**
	 * Returns true if all values which are compatible with the constraints of this instance are also 
	 * compatible with the passed constraints (but not necessary the other way around)
	 * @param TypeConstraint $constraints
	 * @return bool
	 */
	function isPassableTo(TypeConstraint $constraints, $ignoreNullAllowed = false) {
		if ($constraints->isEmpty()) return true;
		 
		if (!(TypeUtils::isTypeA($this->getTypeName(), $constraints->getTypeName()) 
				&& ($ignoreNullAllowed || $constraints->allowsNull() || !$this->allowsNull()))) return false;
				
		$arrayFieldConstraints = $constraints->getArrayFieldTypeConstraint();
		if ($arrayFieldConstraints === null) return true;
		if ($this->arrayFieldTypeConstraint === null) return true;
		
		return $this->arrayFieldTypeConstraint->isPassableTo($arrayFieldConstraints, $ignoreNullAllowed);
	}
	
	function isPassableBy(TypeConstraint $constraints, $ignoreNullAllowed = false) {
		if ($this->isEmpty()) return true;

		if (!(TypeUtils::isTypeA($constraints->getTypeName(), $this->getTypeName())
				&& ($ignoreNullAllowed || $this->allowsNull() || !$constraints->allowsNull()))) return false;
		
		if ($this->arrayFieldTypeConstraint === null) return true;
		$arrayFieldConstraints = $constraints->getArrayFieldTypeConstraint();
		if ($arrayFieldConstraints === null) return true;

		return $this->arrayFieldTypeConstraint->isPassableBy($arrayFieldConstraints, $ignoreNullAllowed);
	}
	
	function getLenientCopy() {
		if (($this->allowsNull && $this->convertable) || $this->isArrayLike()) return $this;
				
		$convertable =  $this->convertable || TypeName::isConvertable($this->typeName);
		
		return new TypeConstraint($this->typeName, true, $this->arrayFieldTypeConstraint, 
				$this->whitelistTypes, $convertable);
	}
	
	function __toString(): string {
		$str = '';
		
		if ($this->allowsNull) {
			$str .= '?';
		}
		
		$str .= $this->typeName;
		
		if ($this->arrayFieldTypeConstraint !== null) {
			$str .= '<' . $this->arrayFieldTypeConstraint . '>';
		}
		
		return $str;
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
	
	private static function createFromExpresion(string $type) {
		$matches = null;
		if (!preg_match('/^(\\?)?([^<>]+)(<(.+)>)?$/', $type, $matches)) {
			throw new \InvalidArgumentException('Invalid TypeConstraint expression: ' . $type);
		}
		
		$typeName = $matches[2];
		
		if (!TypeName::isValid($typeName)) {
			throw new \InvalidArgumentException($type . ' is an invalid TypeConstraint expression. Reason: '
					. $typeName . ' contains invalid characters.');
		}
		
		$allowsNull = $matches[1] == '?';
				
		$arrayFieldTypeConstraint = null;
		if (isset($matches[4])) {
			if (!TypeName::isArrayLike($typeName)) {
				throw new \InvalidArgumentException('Array field generics disabled for ' . $type . '. Reason '
						. $typeName . ' is not arraylike.');
			}
			
			try {
				$arrayFieldTypeConstraint = self::create($matches[4]);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException($type . ' is an invalid TypeConstraint expression. Reason: '
						. $e->getMessage(), 0, $e);
			}
		}
		
		
		return new TypeConstraint($typeName, $allowsNull, $arrayFieldTypeConstraint);
	}
	
	/**
	 * @param string|UnionTypeConstraint $type
	 * @return \n2n\util\type\TypeConstraint
	 */
	public static function create(string|\ReflectionUnionType|UnionTypeConstraint $type) {
		if ($type instanceof UnionTypeConstraint) {
			return $type;
		}
		
		if ($type instanceof \ReflectionUnionType) {
			return new UnionTypeConstraint(array_map(
					fn ($namedType) => TypeConstraint::create($namedType),
					$type->getTypes()));
		}
		
		
		return new UnionTypeConstraint(array_map(
				fn ($typeName) => TypeConstraint::create($typeName), 
				TypeName::extractUnionTypeNames($type)));
	}
	
	/**
	 * @param string|\ReflectionUnionType|UnionTypeConstraint|null $type
	 * @return \n2n\util\type\TypeConstraint
	 */
	public static function build(string|\ReflectionUnionType|UnionTypeConstraint|null $type) {
		if ($type === null) {
			return null;
		}
		
		return self::create($type);
	}
	
	
	
}
