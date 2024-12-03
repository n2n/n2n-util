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

use n2n\util\ex\NotYetImplementedException;
use n2n\util\type\ArgUtils;
use n2n\util\magic\MagicContext;
use n2n\util\magic\MagicObjectUnavailableException;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use n2n\util\magic\MagicLookupFailedException;

class PsrMagicContext implements MagicContext {

	function __construct(private ContainerInterface $container) {
	}

	public function get(string $id) {
		return $this->container->get($id);
	}

	public function has(string|\ReflectionClass $id): bool {
		if ($id instanceof \ReflectionClass) {
			$id = $id->getName();
		}

		return $this->container->has($id);
	}

	public function lookup(string|\ReflectionClass $id, bool $required = true, ?string $contextNamespace = null): mixed {
		if (!$required && !$this->container->has($id)) {
			return null;
		}

		try {
			return $this->container->get($id);
		} catch (NotFoundExceptionInterface $e) {
			throw new MagicObjectUnavailableException($e->getMessage(), 0, $e);
		} catch (ContainerExceptionInterface $e) {
			throw new MagicLookupFailedException($e->getMessage(), 0, $e);
		}
	}
	
}
