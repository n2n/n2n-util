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
namespace n2n\util\io\stream\impl;

use n2n\util\ex\NotYetImplementedException;
use n2n\util\io\stream\InputStream;

class StringInputStream implements InputStream {
	private $str;
	
	public function __construct($str) {
		$this->str = (string) $str;
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\io\stream\InputStream::available()
	 */
	public function available() {
		return strlen($this->str);
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\io\stream\InputStream::read()
	 */
	public function read($length = null) {
		if (isset($length)) {
			return substr($this->str, 0, $length);
		}
		return $this->str;	
	}

	/* (non-PHPdoc)
	 * @see \n2n\util\io\stream\Stream::isOpen()
	 */
	public function isOpen() {
		return true;
	}

	/* (non-PHPdoc)
	 * @see \n2n\util\io\stream\Stream::close()
	 */
	public function close() {
	}
	/**
	 * {@inheritDoc}
	 * @see \n2n\util\io\stream\Stream::hasResource()
	 */
	public function hasResource() {
		throw new NotYetImplementedException();
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\util\io\stream\Stream::getResource()
	 */
	public function getResource() {
		throw new NotYetImplementedException();
	}


}
