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
namespace n2n\util\type\custom;

use n2n\util\type\TypeConstraints;

class Undefined {
	private static Undefined $i;

	private function __construct() {}

	/**
	 * @deprecated use {@link self::val()}
	 */
	static function i(): Undefined {
		return self::val();
	}

	static function val(): Undefined {
		return self::$i ??= new Undefined();
	}

	static function is(mixed $arg): bool {
		return $arg === self::val();
	}

	static function isNot(mixed $arg): bool {
		return !self::is($arg);
	}

	static function initProperties(object $obj): void {
		$class = new \ReflectionClass($obj);
		foreach ($class->getProperties() as $property) {
			if ($property->isInitialized($obj)) {
				continue;
			}

			if (TypeConstraints::type($property->getType())->isPassableBy(TypeConstraints::type(Undefined::class))) {
				$property->setValue($obj, self::val());
			}
		}
	}
}