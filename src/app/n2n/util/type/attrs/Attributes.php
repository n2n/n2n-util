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
namespace n2n\util\type\attrs;

use n2n\util\type\TypeConstraint;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use n2n\util\col\ArrayUtils;
use n2n\util\StringUtils;
use n2n\util\type\TypeUtils;
use n2n\web\http\controller\Interceptor;

class Attributes {
	private $attrs;
	private $interceptor;
	/**
	 * 
	 * @param array $attrs
	 */
	public function __construct(array $attrs = null) {
		$this->attrs = (array) $attrs;
	}
	
	public function setInterceptor(?Interceptor $interceptor) {
		$this->interceptor = $interceptor;
		return $this;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function isEmpty() {
		return empty($this->attrs);
	}
	/**
	 *
	 * @return boolean
	 */
	public function contains($name) {
		return array_key_exists($name, $this->attrs);
	}
	
	public function getNames() {
		return array_keys($this->attrs);
	}
	
	public function hasKey($name, $key) {
		return array_key_exists($name, $this->attrs) 
				&& is_array($this->attrs[$name])
				&& array_key_exists($key, $this->attrs[$name]);
	}
	/**
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function set(string $name, $value) {
		$this->attrs[$name] = $value;
	}
	/**
	 * 
	 * @param string $name
	 * @param mixed $key scalar
	 * @param mixed $value
	 */
	public function add(string $name, $key, $value) {
		if(!isset($this->attrs[$name]) || !is_array($this->attrs[$name])) {
			$this->attrs[$name] = array();
		}
	
		$this->attrs[$name][$key] = $value;
	}
	/**
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function push(string $name, $value) {
		if(!isset($this->attrs[$name]) || !is_array($this->attrs[$name])) {
			$this->attrs[$name] = array();
		}
	
		$this->attrs[$name][] = $value;
	}

	private function findR(&$attrs, array $nextNames, array $prevNames, $mandatory, &$found) {
		if (empty($nextNames)) {
			$found = true;
			return $attrs;
		}
		 
		$nextName = array_shift($nextNames);
		 
		if (!is_array($attrs)) {
			throw new InvalidAttributeException('Property \'' . new AttributePath($prevNames)
				. '\' must be an array. ' . TypeUtils::getTypeInfo($attrs) . ' given.');
		}
		 
		$prevNames[] = $nextName;
		 
		if (!array_key_exists($nextName, $attrs)) {
			if (!$mandatory) {
				$found = false;
				return null;
			}
			throw new MissingAttributeFieldException('Missing property: '  . new AttributePath($prevNames));
		}
		 
		return $this->findR($attrs[$nextName], $nextNames, $prevNames, $mandatory, $found);
		 
	}
	
	private function retrieve($path, $type, $mandatory, $defaultValue = null, &$found = null) {
		$attributePath = AttributePath::create($path);
		$typeConstraint = TypeConstraint::build($type);
		
		$value = $this->findR($this->attrs, $attributePath->toArray(), array(), $mandatory, $found);
		
		if (!$found) return $defaultValue;
		
		if ($typeConstraint === null) {
			return $value;
		}
		
		try {
			$typeConstraint->validate($value);
		} catch (ValueIncompatibleWithConstraintsException $e) {
			throw new InvalidAttributeException('Property contains invalid value: ' . $attributePath, 0, $e);
		}
		
		return $value;
	}

	
	/**
	 * @param string|AttributePath|array $path
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @deprecated use {@see self::req()} or {@see self::opt()}
	 */
	public function get($path, bool $mandatory = true, $defaultValue = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->req($path, TypeConstraint::createSimple(null, $nullAllowed));
		}
		
		return $this->opt($path, TypeConstraint::createSimple(null, $nullAllowed), $defaultValue);
	}
	
	/**
	 * @param string $name
	 * @param bool $mandatory
	 * @param mixed $defaultValue
	 * @param TypeConstraint $typeConstraint
	 * @throws InvalidAttributeException
	 * @return mixed
	 */
	public function req($path, $type = null) {
		return $this->retrieve($path, $type, true);
	}
	
	public function opt($path, $type = null, $defaultValue = null) {
		return $this->retrieve($path, $type, false, $defaultValue);
	}
	
	/**
	 * @param string|AttributePath|array $path
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @deprecated use {@see self::reqScalar()} or {@see self::optScalar()}
	 */
	public function getScalar($path, bool $mandatory = true, $defaultValue = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqScalar($path, $nullAllowed);
		}
		
		return $this->optScalar($path, $defaultValue, $nullAllowed);
	}
	
	public function reqScalar($path, bool $nullAllowed = false) {
		return $this->req($path, TypeConstraint::createSimple('scalar', $nullAllowed));
	}
	
	public function optScalar($path, $defaultValue = null, bool $nullAllowed = true) {
		return $this->opt($path, TypeConstraint::createSimple('scalar', $nullAllowed));
	}
	
	/**
	 * @param string|AttributePath|array $path
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @deprecated use {@see self::reqString()} or {@see self::optString()}
	 */
	public function getString($path, bool $mandatory = true, $defaultValue = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqString($path, $nullAllowed);
		}
		
		return $this->optString($path, $defaultValue, $nullAllowed); 
	}
	
	public function reqString($name, bool $nullAllowed = false, bool $lenient = true) {
		if (!$lenient) {
			return $this->req($name, TypeConstraint::createSimple('string', $nullAllowed));
		}
		
		if (null !== ($value = $this->reqScalar($name, $nullAllowed))) {
			return (string) $value;
		}
		
		return null;
	}
	
	public function optString($path, $defaultValue = null, $nullAllowed = true, bool $lenient = true) {
		if (!$lenient) {
			return $this->opt($path, TypeConstraint::createSimple('string', $nullAllowed), $defaultValue);
		}
		
		if (null !== ($value = $this->optScalar($path, $defaultValue, $nullAllowed))) {
			return (string) $value;
		}
		
		return null;
	}
	
	/**
	 * @param string|AttributePath|array $path
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @deprecated use {@see self::reqBool()} or {@see self::optBool()}
	 */
	public function getBool($path, bool $mandatory = true, $defaultValue = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqBool($path, $nullAllowed);
		}
		
		return $this->optBool($path, $defaultValue, $nullAllowed);
	}
	
	public function reqBool($path, bool $nullAllowed = false, $lenient = true) {
		if (!$lenient) {
			return $this->req($path, TypeConstraint::createSimple('bool', $nullAllowed));
		}
		
		if (null !== ($value = $this->reqScalar($path, $nullAllowed))) {
			return (bool) $value;
		}
		
		return null;
	}
	
	public function optBool($path, $defaultValue = null, bool $nullAllowed = true, $lenient = true) {
		if (!$lenient) {
			return $this->opt($path, TypeConstraint::createSimple('bool', $nullAllowed), $defaultValue);
		}
		
		if (null !== ($value = $this->optScalar($path, $defaultValue, $nullAllowed))) {
			return (bool) $value;
		}
		
		return $defaultValue;
	}
	
	public function reqNumeric($path, bool $nullAllowed = false) {
		return $this->req($path, TypeConstraint::createSimple('numeric', $nullAllowed));
	}
	
	public function optNumeric($path, $defaultValue = null, bool $nullAllowed = true) {
		return $this->opt($path, TypeConstraint::createSimple('numeric', $nullAllowed), $defaultValue);
	}
	
	public function reqInt($path, bool $nullAllowed = false, $lenient = true) {
		if (!$lenient) {
			return $this->req($path, TypeConstraint::createSimple('int', $nullAllowed));
		}
		
		if (null !== ($value = $this->reqNumeric($path))) {
			return (int) $value;
		}
		
		return null;
	}
	
	public function optInt($path, $defaultValue = null, bool $nullAllowed = true, $lenient = true) {
		if (!$lenient) {
			return $this->opt($path, TypeConstraint::createSimple('int', $nullAllowed), $defaultValue);
		}
		
		if (null !== ($value = $this->optNumeric($path, $defaultValue))) {
			return (int) $value;
		}
			
		return null;
	}
	
	public function reqEnum($path, array $allowedValues, bool $nullAllowed = false) {
		return $this->getEnum($path, $allowedValues);
	}
	
	public function optEnum($path, array $allowedValues, $defaultValue = null, bool $nullAllowed = true) {
		return $this->getEnum($path, $allowedValues, false, $defaultValue, $nullAllowed);
	}
	
	private function getEnum($path, array $allowedValues, $mandatory = true, $defaultValue = null, $nullAllowed = false) {
		$found = null;
		$value = $this->retrieve($path, null, $mandatory, $defaultValue, $found);
		if (!$found) return $defaultValue;
	
		if ($nullAllowed && $value === null) {
			return $value;
		}
		
		if (!ArrayUtils::inArrayLike($value, $allowedValues)) {
			throw new InvalidAttributeException('Property \'' . $attributePath 
				. '\' must contain one of following values: ' . implode(', ', $allowedValues) 
				. '. Given: ' . TypeUtils::buildScalar($value));
		}
	
		return $value;
	}
	
	/**
	 * @param string|AttributePath|array $path
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param TypeConstraint|string|null $fieldType
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @deprecated use {@see self::reqArray()} or {@see self::optArray()}
	 */
	public function getArray($path, bool $mandatory = true, $defaultValue = array(), $fieldType = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqArray($path, $fieldType, $nullAllowed);
		}
		
		return $this->optArray($path, $fieldType, $defaultValue, $nullAllowed);
	}
	
	public function reqArray($name, $fieldType = null, bool $nullAllowed = false) {
		return $this->req($name, TypeConstraint::createArrayLike('array', $nullAllowed, $fieldType));
	}
	
	public function optArray($name, $fieldType = null, $defaultValue = [], bool $nullAllowed = false) {
		return $this->opt($name, TypeConstraint::createArrayLike('array', $nullAllowed, $fieldType), $defaultValue);
	}
	
	/**
	 * @param string|AttributePath|array $path
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @deprecated use {@see self::reqScalarArray()} or {@see self::optScalarArray()}
	 */
	public function getScalarArray($path, bool $mandatory = true, $defaultValue = array(), bool $nullAllowed = false, bool $fieldNullAllowed = true) {
		if ($mandatory) {
			return $this->reqScalarArray($path, $nullAllowed, $fieldNullAllowed);
		}
		
		return $this->optScalarArray($path, $defaultValue, $nullAllowed, $fieldNullAllowed);
	}
	
	public function reqScalarArray($name, bool $nullAllowed = false, bool $fieldNullAllowed = false) {
		return $this->reqArray($name, TypeConstraint::createSimple('scalar', $fieldNullAllowed), $nullAllowed);
	}
	
	public function optScalarArray($name, $defaultValue = [], bool $nullAllowed = false, bool $fieldNullAllowed = false) {
		return $this->optArray($name, TypeConstraint::createSimple('scalar', $fieldNullAllowed), $defaultValue, $nullAllowed);
	}
	
	/**
	 * 
	 * @param string $name
	 */
	public function remove(string $name) {
		unset($this->attrs[$name]);
	}
	
	/**
	 * @param string $name
	 * @param mixed $key scalar
	 */
	public function removeKey(string $name, $key) {
		if ($this->hasKey($name, $key)) {
			unset($this->attrs[$name][$key]);
		}
	}
	/**
	 * 
	 * @param array $attrs
	 */
	public function setAll(array $attrs) {
		$this->attrs = $attrs;
	}
	/**
	 * 
	 * @return array
	 */
	public function toArray() {
		return $this->attrs;
	}
	/**
	 * 
	 * @param Attributes $attributes
	 */
	public function append(Attributes $attributes) {
		$this->appendAll($attributes->toArray());
	}
	/**
	 * 
	 * @param array $attrs
	 */
	public function appendAll(array $attrs, bool $ignoreNull = false) {
		foreach ($attrs as $key => $value) {
			if ($ignoreNull && $value === null) continue;
			
			if (is_array($value) && isset($this->attrs[$key]) && is_array($this->attrs[$key])) {
				$value = array_merge($this->attrs[$key], $value);
// 				$value = $this->merge($this->attrs[$key], $value);
			}
			
			$this->attrs[$key] = $value;
		}
	}
	
	public function removeNulls(bool $recursive = false) {
		$this->removeNullsR($this->attrs, $recursive);
	}
	
	private function removeNullsR(array &$attrs, bool $recursive = false) {
		foreach ($attrs as $key => $value) {
			if (!isset($attrs[$key])) {
				unset($attrs[$key]);
			} else if ($recursive && is_array($attrs[$key])) {
				$this->removeNullsR($attrs[$key], true);
			}
		}
	}
	/**
	 * 
	 * @param array $attrs
	 * @param array $attrs2
	 */
	protected function merge(array $attrs, array $attrs2) {
		foreach ($attrs2 as $key => $value) {
			if (is_numeric($key)) {
				$attrs[] = $attrs2[$key];
				continue;
			}
				
			if (!array_key_exists($key, $attrs)) {
				$attrs[$key] = $value;
				continue;
			}
				
			if (is_array($attrs[$key])) {
				$attrs[$key] = $this->merge($attrs[$key], $attrs2[$key]);
				continue;
			}
				
			$attrs[$key] = $value;
		}
	
		return $attrs;
	}
	/**
	 * 
	 * @return string
	 */
	public function serialize() {
		return serialize($this->attrs);
	}
	/**
	 * 
	 * @param string $serialized
	 * @param \n2n\util\UnserializationFailedException
	 */
	public static function createFromSerialized($serialized) {
		$attrs = StringUtils::unserialize($serialized);
		if (!is_array($attrs)) $attrs = array();
		return new Attributes($attrs);
	}
}

interface AttributesInterceptor {
	function decorateException(AttributesException $e): \Throwable;
}
