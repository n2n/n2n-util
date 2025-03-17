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
	private $whitelistTypes = [];

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

	function allowsNull(): bool {
		foreach ($this->namedTypeConstraints as $namedTypeConstraint) {
			if ($namedTypeConstraint->allowsNull()) {
				return true;
			}
		}

		return false;
	}

	function isValueValid(mixed $value): bool {
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
	
	function validate(mixed $value): mixed {
		foreach ($this->whitelistTypes as $whitelistType) {
			if (TypeUtils::isValueA($value, $whitelistType, false)) {
				return $value;
			}
		}
		
		foreach ($this->namedTypeConstraints as $namedTypeConstraint) {
			if ($namedTypeConstraint->isValueValid($value)) {
				return $namedTypeConstraint->validate($value);
			}
		}
		
		throw $this->createIncompatbleValueException($value);
	}
	
	
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\util\type\TypeConstraint::getNamedTypeConstraints()
	 */
	function getNamedTypeConstraints(): array {
		return $this->namedTypeConstraints;
	}

	function isMixed(): bool {
		$namedTypeConstraints = $this->getNamedTypeConstraints();
		if (empty($namedTypeConstraints)) {
			return true;
		}

		foreach ($namedTypeConstraints as $namedTypeConstraint) {
			if ($namedTypeConstraint->isMixed()) {
				return true;
			}
		}

		return false;
	}


	public function isPassableTo(TypeConstraint $constraint, bool $ignoreNullAllowed = false): bool {
		$toNamedTypeConstraints = $constraint->getNamedTypeConstraints();
		foreach ($this->namedTypeConstraints as $namedTypeConstraint) {
			$passable = false;

			foreach ($toNamedTypeConstraints as $toNamedTypeConstraint) {
				if ($namedTypeConstraint->isPassableTo($toNamedTypeConstraint, $ignoreNullAllowed)) {
					$passable = true;
					break;
				}
			}

			if (!$passable) {
				return false;
			}
		}

		return true;
	}

	function isPassableBy(TypeConstraint $constraint, bool $ignoreNullAllowed = false): bool {
		foreach ($constraint->getNamedTypeConstraints() as $byNamedTypeConstraint) {
			$passable = false;

			foreach ($this->namedTypeConstraints as $namedTypeConstraint) {
				if ($namedTypeConstraint->isPassableBy($byNamedTypeConstraint, $ignoreNullAllowed)) {
					$passable = true;
					break;
				}
			}

			if (!$passable) {
				return false;
			}
		}

		return true;
	}

	function getLenientCopy() {
		return new UnionTypeConstraint(array_map(fn ($ntc) => $ntc->getLenientCopy(), $this->namedTypeConstraints));
	}
	
	function __toString(): string {
		return TypeName::concatUnionTypeNames(array_map(fn ($ntc) => $ntc->getTypeName(), $this->namedTypeConstraints));
	}
	
	/**
	 * @param string|\ReflectionUnionType|array $type
	 * @return \n2n\util\type\UnionTypeConstraint
	 */
	public static function from(string|\ReflectionUnionType|array $type, bool $convertable = false) {
		if ($type instanceof \ReflectionUnionType) {
			return new UnionTypeConstraint(array_map(
					fn ($namedType) => NamedTypeConstraint::from($namedType, $convertable),
					$type->getTypes()));
		}

		if (is_array($type)) {
			ArgUtils::valArray($type, 'string|null');
			return new UnionTypeConstraint(array_map(
					fn ($iType) => NamedTypeConstraint::from($iType, $convertable),
					$type));
		}
		
		return new UnionTypeConstraint(array_map(
				fn ($typeName) => NamedTypeConstraint::from($typeName, $convertable), 
				TypeName::extractUnionTypeNames($type)));
	}
	
	
	
	
}
