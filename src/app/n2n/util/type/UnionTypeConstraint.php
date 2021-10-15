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

use n2n\util\col\ArrayUtils;
use n2n\util\ex\IllegalStateException;

class UnionTypeConstraint extends TypeConstraint {	
	private $namedTypeConstraints = [];

	/**
	 * @param NamedTypeConstraint[] $namedTypeConstraints
	 * @param array $whitelistTypes
	 */
	protected function __construct(array $namedTypeConstraints = [], array $whitelistTypes = array()) {
		ArgUtils::valArray($namedTypeConstraints, NamedTypeConstraint::class);
		$this->namedTypeConstraints = $namedTypeConstraints;
		$this->whitelistTypes = $whitelistTypes;
	}
	
	/**
	 * @return TypeConstraint[]
	 */
	function getTypeConstraints() {
		return $this->namedTypeConstraints;
	}
	
	function isValueValid($value): bool {
		foreach ($this->whitelistTypes as $whitelistType) {
			if (TypeUtils::isValueA($value, $whitelistType, false)) return true;
		}
		
		foreach ($this->namedTypeConstraints as $namedTypeConstraint) {
			if ($namedTypeConstraint->isValueValid($value)) {
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
	
	
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\util\type\TypeConstraint::getNamedTypeConstraints()
	 */
	function getNamedTypeConstraints(): array {
		return $this->namedTypeConstraints;
	}
	
	function getLenientCopy() {
		return new UnionTypeConstraint(array_map(fn ($ntc) => $ntc->getLenientCopy(), $this->namedTypeConstraints));
	}
	
	function __toString(): string {
		return TypeName::concatUnionTypeNames(array_map(fn ($ntc) => $ntc->getTypeName(), $this->namedTypeConstraints));
	}
	
	/**
	 * @param string|\ReflectionUnionType $type
	 * @return \n2n\util\type\UnionTypeConstraint
	 */
	public static function from(string|\ReflectionUnionType $type, bool $convertable = false) {
		if ($type instanceof \ReflectionUnionType) {
			return new UnionTypeConstraint(array_map(
					fn ($namedType) => NamedTypeConstraint::from($namedType, $convertable),
					$type->getTypes()));
		}
		
		return new UnionTypeConstraint(array_map(
				fn ($typeName) => NamedTypeConstraint::from($typeName, $convertable), 
				TypeName::extractUnionTypeNames($type)));
	}
	
	
	
	
}
