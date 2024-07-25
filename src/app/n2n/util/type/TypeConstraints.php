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

use ReflectionClass;

class TypeConstraints {
	
	/**
	 * @param bool $nullable
	 * @return TypeConstraint
	 */
	static function scalar(bool $nullable = false) {
		return NamedTypeConstraint::createSimple('scalar', $nullable);
	}

	/**
	 * @param bool $nullable
	 * @param bool $convertable
	 * @return TypeConstraint
	 */
	static function string(bool $nullable = false, bool $convertable = false) {
		return NamedTypeConstraint::createSimple('string', $nullable, $convertable);
	}

	/**
	 * @param bool $nullable
	 * @param bool $convertable
	 * @return TypeConstraint
	 */
	static function int(bool $nullable = false, bool $convertable = false) {
		return NamedTypeConstraint::createSimple('int', $nullable, $convertable);
	}

	/**
	 * @param bool $nullable
	 * @param bool $convertable
	 * @return TypeConstraint
	 */
	static function float(bool $nullable = false, bool $convertable = false) {
		return NamedTypeConstraint::createSimple('float', $nullable, $convertable);
	}

	/**
	 * @param bool $nullable
	 * @param bool $convertable
	 * @return TypeConstraint
	 */
	static function bool(bool $nullable = false, bool $convertable = false) {
		return NamedTypeConstraint::createSimple('bool', $nullable, $convertable);
	}
	
	/**
	 * @param bool $nullable
	 * @return TypeConstraint
	 */
	static function mixed(bool $nullable = false) {
		return NamedTypeConstraint::createSimple(TypeName::PSEUDO_MIXED, $nullable);
	}

	/**
	 * @param string|\ReflectionType|\ReflectionParameter|ReflectionClass|array|null $type
	 * @param bool $convertable
	 * @return TypeConstraint
	 */
	static function type(string|\ReflectionType|\ReflectionParameter|\ReflectionClass|array|null $type, bool $convertable = false) {
		if ($type instanceof \ReflectionParameter) {
			$type = $type->getType();
		}

		if ($type === null || $type instanceof ReflectionClass) {
			return NamedTypeConstraint::createSimple($type, true, $convertable);
		}

		if (is_array($type) || TypeName::isUnionType($type)) {
			return UnionTypeConstraint::from($type, $convertable);
		}
		
		return NamedTypeConstraint::from($type, $convertable);
	}

	/**
	 * @param string|ReflectionClass|null $type
	 * @param bool $allowsNull
	 * @param bool $convertable
	 * @return NamedTypeConstraint
	 */
	static function namedType(string|ReflectionClass|null $type, bool $allowsNull = true, bool $convertable = false) {
		return NamedTypeConstraint::createSimple($type, $allowsNull, $convertable);
	}
	
	/**
	 * @param bool $nullable
	 * @param TypeConstraint|string $fieldTypeConstraint
	 * @return TypeConstraint
	 */
	static function array(bool $nullable = false, $fieldTypeConstraint = null,
			string|\ReflectionClass|TypeConstraint|null $keyTypeConstraint = null) {
		return TypeConstraint::createArrayLike('array', $nullable, TypeConstraint::build($fieldTypeConstraint),
				arrayKeyType: TypeConstraint::build($keyTypeConstraint));
	}
	
	/**
	 * @param bool $nullable
	 * @param TypeConstraint|string $fieldTypeConstraint
	 * @return TypeConstraint
	 */
	static function arrayObject(bool $nullable, $fieldTypeConstraint = null) {
		return TypeConstraint::createArrayLike('ArrayObject', $nullable, TypeConstraint::build($fieldTypeConstraint));
	}

	/**
	 * @param bool $nullable
	 * @param TypeConstraint|string $fieldTypeConstraint
	 * @return TypeConstraint
	 */
	static function arrayLike(bool $nullable, $fieldTypeConstraint = null) {
		return TypeConstraint::createArrayLike(null, $nullable, TypeConstraint::build($fieldTypeConstraint));
	}
}