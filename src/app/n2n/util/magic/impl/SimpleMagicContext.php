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
namespace n2n\util\magic\impl;

use n2n\util\type\ArgUtils;
use n2n\util\magic\MagicContext;
use n2n\util\magic\MagicObjectUnavailableException;

class SimpleMagicContext implements MagicContext {

	function __construct(private array $objs) {
		ArgUtils::valArray($this->objs, ['object']);
	}

	public function get(string $id) {
		return $this->lookup($id, true);
	}

	public function set(string $id, object $obj): static {
		$this->objs[$id] = $obj;
		return $this;
	}

	function setCallback(string $id, \Closure $closure): static {
		$this->objs[$id] = $closure;
		return $this;
	}

	public function has(string|\ReflectionClass $id): bool {
		if ($id instanceof \ReflectionClass) {
			$id = $id->getName();
		}

		return isset($this->objs[$id]);
	}

	public function lookup(string|\ReflectionClass $id, bool $required = true, ?string $contextNamespace = null): mixed {
		if ($id instanceof \ReflectionClass) {
			$id = $id->getName();
		}

		if (isset($this->objs[$id])) {
			if (!($this->objs[$id] instanceof \Closure)) {
				return $this->objs[$id];
			}

			$result = $this->objs[$id]();
			ArgUtils::valObjectReturn($result, null, $this->objs[$id]);
			return $result;
		}

		if ($required) {
			throw new MagicObjectUnavailableException('Unknown id: ' . $id);
		}

		return null;
	}

}
