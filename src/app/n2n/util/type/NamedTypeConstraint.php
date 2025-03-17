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
use n2n\reflection\ReflectionUtils;

class NamedTypeConstraint extends TypeConstraint {	
	private $typeName;
	private $allowsNull;
	private $arrayFieldTypeConstraint;
	private $whitelistTypes;
	private $convertable = false;

	/**
	 * @param string $typeName
	 * @param bool $allowsNull
	 * @param TypeConstraint|null $arrayFieldTypeConstraint
	 * @param array $whitelistTypes
	 * @param bool $convertable
	 * @param TypeConstraint|null $arrayKeyTypeConstraint
	 */
	protected function __construct(string $typeName, bool $allowsNull,
			?TypeConstraint $arrayFieldTypeConstraint = null, array $whitelistTypes = array(), bool $convertable = false,
			private ?TypeConstraint $arrayKeyTypeConstraint = null) {
		$this->typeName = $typeName;
		$this->allowsNull = $allowsNull || TypeName::isNullable($typeName);
		$this->convertable = $convertable && TypeName::isConvertable($typeName);
		$this->arrayFieldTypeConstraint = $arrayFieldTypeConstraint;
		$this->whitelistTypes = $whitelistTypes;
	}

	function isMixed(): bool {
		return $this->typeName === TypeName::PSEUDO_MIXED;
	}
	
	public function setWhitelistTypes(array $whitelistTypes) {
		$this->whitelistTypes = $whitelistTypes;
		return $this;
	}
	
	public function getWhitelistTypes() {
		return $this->whitelistTypes;
	}
	
	/**
	 * @return bool
	 */
	public function isConvertable() {
		return $this->convertable;
	}
	
	/**
	 * @param bool $convertable
	 * @throws IllegalStateException
	 * @return NamedTypeConstraint
	 */
	public function setConvertable(bool $convertable) {
		if ($convertable && !TypeName::isConvertable($this->typeName)) {
			throw new IllegalStateException('Values are not convertable to ' . $this->typeName);
		}
		
		$this->convertable = $convertable;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getTypeName() {
		return $this->typeName;
	}
// 	/**
// 	 * @return boolean
// 	 */
// 	public function isArray() {
// 		return $this->type == 'array';
// 	}
	
	public function isArrayLike() {
		return $this->arrayFieldTypeConstraint !== null;
	}
	/**
	 * @return boolean
	 */
	function allowsNull(): bool {
		return $this->allowsNull;
	}
	
	/**
	 * @return boolean
	 */
	public function isTypeSafe() {
		if (!TypeName::isSafe($this->typeName)) {
			return false;
		}
		
		if (!$this->isArrayLike()) {
			return true;
		}
		
		return $this->arrayFieldTypeConstraint->isTypeSafe();
	}
	
	public function isScalar() {
		return !$this->isArrayLike() && TypeName::isScalar($this->typeName);
	}
	/**
	 * @return TypeConstraint|null
	 */
	public function getArrayFieldTypeConstraint() {
		return $this->arrayFieldTypeConstraint;
	}
	
// 	public function getWhitelistTypes() {
// 		return $this->whitelistTypes;
// 	}
	
	public function isValueValid(mixed $value): bool {
		foreach ($this->whitelistTypes as $whitelistType) {
			if (TypeUtils::isValueA($value, $whitelistType, false)) return true;
		}
		
		if ($value === null) {
			return $this->allowsNull();
		}
		
		if (!TypeName::isValueA($value, $this->typeName, false)) {
			if (!$this->convertable) {
				return false;
			}
			
			return TypeName::isValueConvertTo($value, $this->typeName);
		}
		
		if (!$this->isArrayLike()) return true;
		
		if (!ArrayUtils::isArrayLike($value)) {
			throw new IllegalStateException('Illegal constraint ' . $this->__toString() . ' defined:'
					. $this->typeName . ' is not array like.');
		}

		if ($this->arrayFieldTypeConstraint->isMixed()) {
			return true;
		}
		
		foreach ($value as $fieldKey => $fieldValue) {
			if (!$this->arrayFieldTypeConstraint->isValueValid($fieldValue)
					|| ($this->arrayKeyTypeConstraint !== null && !$this->arrayKeyTypeConstraint->isValueValid($fieldKey))) {
				return false;
			}
		}
		
		return true;
	}
	
	public function validate(mixed $value): mixed {
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
		
		if (!TypeName::isValueA($value, $this->typeName, false)) {
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

		// Do not touch array if the array field constraint is mixed anyway. This way an ArrayObjectProxy will not be
		// initialized.
		if ($this->arrayFieldTypeConstraint->isMixed()) {
			return $value;
		}
		
		foreach ($value as $key => $fieldValue) {
			try {
				$value[$key] = $this->arrayFieldTypeConstraint->validate($fieldValue);
			} catch (ValueIncompatibleWithConstraintsException $e) {
				throw new ValueIncompatibleWithConstraintsException(
						'Value type not allowed with constraints '
						. $this->__toString() . '. Array field (key: \'' . $key . '\') contains invalid value.', 0, $e);
			}
		}

		if ($this->arrayKeyTypeConstraint === null) {
			return $value;
		}

		$copiedArray = [];
		foreach ($value as $key => $fieldValue) {
			try {
				$newKey = $this->arrayKeyTypeConstraint->validate($key);
			} catch (ValueIncompatibleWithConstraintsException $e) {
				throw new ValueIncompatibleWithConstraintsException(
						'Value type not allowed with constraints '
						. $this->__toString() . '. Array field contains invalid key: ' . $key, 0, $e);
			}

			if (!is_scalar($newKey)) {
				throw new IllegalStateException('Array key TypeConstraint is not scalar: ' .
						$this->arrayKeyTypeConstraint);
			}

			$copiedArray[$newKey] = $fieldValue;
		}
		return $copiedArray;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\util\type\TypeConstraint::getNamedTypeConstraints()
	 */
	function getNamedTypeConstraints(): array {
		return [$this];
	}
	
	public function isEmpty() {
		return $this->typeName === TypeName::PSEUDO_MIXED && $this->allowsNull 
				&& ($this->arrayFieldTypeConstraint === null || $this->arrayFieldTypeConstraint->isEmpty());
	}
	/**
	 * Returns true if all values which are compatible with the constraints of this instance are also 
	 * compatible with the passed constraints (but not necessary the other way around)
	 * @param TypeConstraint $constraints
	 * @return bool
	 */
	public function isPassableTo(TypeConstraint $constraint, bool $ignoreNullAllowed = false): bool {
		foreach ($constraint->getNamedTypeConstraints() as $namedTypeConstraint) {
			if ($namedTypeConstraint->isEmpty()) {
				return true;
			}

			if (!(TypeUtils::isTypeA($this->getTypeName(), $namedTypeConstraint->getTypeName())
					&& ($ignoreNullAllowed || $namedTypeConstraint->allowsNull() || !$this->allowsNull()))) {
				continue;
			}

			$arrayFieldConstraints = $namedTypeConstraint->getArrayFieldTypeConstraint();
			if ($arrayFieldConstraints === null) {
				return true;
			}
			if ($this->arrayFieldTypeConstraint === null) {
				return true;
			}

			if ($this->arrayFieldTypeConstraint->isPassableTo($arrayFieldConstraints, $ignoreNullAllowed)) {
				return true;
			}
		}

		return false;
	}

	public function isPassableBy(TypeConstraint $constraint, bool $ignoreNullAllowed = false): bool {
		if ($this->isEmpty()) return true;

		foreach ($constraint->getNamedTypeConstraints() as $namedTypeConstraint) {
			if (!(TypeUtils::isTypeA($namedTypeConstraint->getTypeName(), $this->getTypeName())
					&& ($ignoreNullAllowed || $this->allowsNull() || !$namedTypeConstraint->allowsNull()))) {
				return false;
			}

			if ($this->arrayFieldTypeConstraint === null) {
				continue;
			}

			$arrayFieldConstraints = $namedTypeConstraint->getArrayFieldTypeConstraint();
			if ($arrayFieldConstraints === null) {
				continue;
			}

			if (!$this->arrayFieldTypeConstraint->isPassableBy($arrayFieldConstraints, $ignoreNullAllowed)) {
				return false;
			}
		}

		return true;
	}
	
	public function getLenientCopy() {
		if (($this->allowsNull && $this->convertable) || $this->isArrayLike()) return $this;
				
		$convertable = $this->convertable || TypeName::isConvertable($this->typeName);
		
		return new NamedTypeConstraint($this->typeName, true, $this->arrayFieldTypeConstraint, 
				$this->whitelistTypes, $convertable);
	}
	
	public function __toString(): string {
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
	
	
	/**
	 * @param \ReflectionParameter $parameter
	 * @return TypeConstraint
	 * @deprecated
	 */
	public static function createFromParameter(\ReflectionParameter $parameter) {
		if (ReflectionUtils::isArrayParameter($parameter)) {
			return new NamedTypeConstraint(TypeName::ARRAY, $parameter->allowsNull(),
					new NamedTypeConstraint(TypeName::PSEUDO_MIXED, true));
		}
		
		$class = ReflectionUtils::extractParameterClass($parameter);
		if ($class !== null && TypeName::isClassArrayLike($class)) {
			return new NamedTypeConstraint($class->getName(), $parameter->allowsNull(),
					new NamedTypeConstraint(TypeName::PSEUDO_MIXED, true));
		}
		
		$typeName = null;
		if (null !== ($type = $parameter->getType())) {
			$typeName = $type->getName();	
		}
		return new NamedTypeConstraint($typeName ?? TypeName::PSEUDO_MIXED, $parameter->allowsNull());
	}
	
	
	private static function createFromExpresion(?string $type, bool $convertable) {
		if ($type === null) {
			return new NamedTypeConstraint('null', true);
		}

		ArgUtils::assertTrue(!TypeName::isUnionType($type));
		
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

		return new NamedTypeConstraint($typeName, $allowsNull, $arrayFieldTypeConstraint, [], $convertable);
	}

	static function from(string|\ReflectionNamedType|\ReflectionClass|null $type, bool $convertable = false): NamedTypeConstraint {
		if ($type instanceof ReflectionClass) {
			return self::createSimple($type, false, false);
		}
		
		if ($type instanceof \ReflectionNamedType) {
			return self::createSimple($type->getName(), $type->allowsNull(), $convertable);
		}
		
		return self::createFromExpresion($type, $convertable);
	}
}
