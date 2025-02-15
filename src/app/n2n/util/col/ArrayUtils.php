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
namespace n2n\util\col;

use n2n\util\type\ArgUtils;
use n2n\util\StringUtils;
use n2n\util\type\TypeName;

class ArrayUtils {

	/**
	 * @param array|\ArrayObject $arrayLike TODO: (\IteratorAggregate&\ArrayAccess&\Countable) when php 8.2
	 * @param bool $required
	 * @return mixed|null
	 */
	public static function shift(array|\ArrayObject &$arrayLike, bool $required = false) {
		if ($required && (!count($arrayLike))) {
			throw new \OutOfRangeException('Array empty.');
		}

		if (is_array($arrayLike)) {
			return array_shift($arrayLike);
		}

		$array = (array) $arrayLike;
		$firstKey = array_key_first($array);

		if ($firstKey === null) {
			return null;
		}

		$get = $array[$firstKey];
		$arrayLike->offsetUnset($firstKey);
		return $get;

	}

	public static function first(iterable $arrayLike): mixed {
		foreach ($arrayLike as $value) {
			return $value;
		}
		return null;
	}

	public static function last(iterable $arrayLike): mixed {
		if (is_array($arrayLike) && !empty($arrayLike)) {
			return end($arrayLike);
		}

		$last = null;
		foreach ($arrayLike as $value) {
			$last = $value;
		}
		return $last;
	}

	public static function reset(array &$array) {
		if (false !== ($result = reset($array))) {
			return $result;
		}

		return null;
	}

	/**
	 * @param array $array
	 * @return mixed|null
	 */
	public static function current(array $array): mixed {
		$refArr = $array;
		if (false !== ($result = current($refArr))) {
			return $result;
		}

		return null;
	}

	public static function end(array &$array) {
		if (false !== ($result = end($array))) {
			return $result;
		}

		return null;
	}
	/**
	 * Add the value to the collection if it does not yet exist.
	 *
	 * @param array|\ArrayObject $collection TODO: ArrayObject to (\IteratorAggregate&\ArrayAccess&\Countable) when php 8.2
	 * @param mixed $needle
	 * @param bool $strict determines if strict comparison (===) should be used during the search.
	 * @return bool whether the value could have been removed or not. false if the value does not
	 *
	 */
	static function unsetByValue(array|\ArrayObject &$arrayLike, mixed $needle, bool $strict = true): bool {
		if (is_array($arrayLike)) {
			$removableKeys = array_keys($arrayLike, $needle, $strict);
			foreach ($removableKeys as $key) {
				unset($arrayLike[$key]);
			}
			return !empty($removableKeys);
		}

		$found = false;
		foreach ($arrayLike as $key => $object) {
			if (($strict && $needle === $object)
					|| (!$strict && $needle == $object)) {
				unset($arrayLike[$key]);
				$found = true;
			}
		}
		return $found;
	}

	public static function isArrayLike($value): bool {
		return TypeName::isValueArrayLike($value);
	}

	public static function isClassArrayLike(\ReflectionClass $class): bool {
		return TypeName::isClassArrayLike($class);
	}

	public static function isTypeNameArrayLike(string $typeName): bool {
		return TypeName::isArrayLike($typeName);
	}

	public static function inArrayLike($needle, $arrayLike): bool {
		ArgUtils::valArrayLike($arrayLike);

		foreach ($arrayLike as $value) {
// 			if ($value === $needle) return true;

			if ($value === null || $needle === null || is_object($needle) || is_object($value)
					|| is_array($needle) || is_array($value)) {
				if ($value === $needle) return true;
				continue;
			}

			if (StringUtils::doEqual($value, $needle)) return true;
		}

		return false;
	}

	static function insertBeforeKey(array &$arr, string $beforeKey, array $values): void {
		$newArr = [];
		foreach ($arr as $key => $value) {
			if ($key === $beforeKey) {
				$newArr = array_merge($newArr, $values);
				$values = [];
			}

			if (!array_key_exists($key, $newArr)) {
				$newArr[$key] = $value;
			}
		}

		$arr = array_merge($newArr, $values);
	}

	/**
	 * Add the value to the collection if it does not yet exist.
	 *
	 * @param array|\ArrayObject $collection
	 * @param mixed $value
	 * @param bool $strict determines if strict comparison (===) should be used during the search.
	 * @return bool whether the value could have been added or not. false if the value already
	 *    exists in the collection.
	 */
	static function uniqueAdd(array|\ArrayObject &$collection, mixed $value, bool $strict = true): bool {
		if (self::contains($collection, $value, $strict)) {
			return false;
		}

		if (is_array($collection)){
			$collection[] = $value;
			return true;
		}

		$collection->append($value);
		return true;
	}

	/**
	 * Overwrites passed collection with newCollection by reference, makes it unique and triggers necessary callbacks
	 * at the end.
	 *
	 * @param array|\ArrayObject $collection
	 * @param array|\ArrayObject $newCollection
	 * @param \Closure|null $addedCallback will be called for every value present in newCollection but not
	 *        in collection.
	 * @param \Closure|null $removedCallback will be called for every value present in collection but not in
	 *        newCollection.
	 * @param bool $strict determines if strict comparison (===) should be used during the search.
	 * @return void
	 */
	static function uniqueExchange(array|\ArrayObject &$collection, array|\ArrayObject $newCollection,
			\Closure|null $addedCallback, \Closure|null $removedCallback, bool $strict = true): void {

	}

	/**
	 * @param array|\ArrayObject $collection
	 * @param array|\ArrayObject $newCollection
	 * @param \Closure|null $addedCallback will be called for every value present in newCollection but not
	 * 		in collection.
	 * @param \Closure|null $removedCallback will be called for every value present in collection but not in
	 * 		newCollection.
	 * @param bool $strict determines if strict comparison (===) should be used during the search.
	 * @return void
	 */
	static function diffWalk(array|\ArrayObject $collection, array|\ArrayObject $newCollection,
			\Closure|null $addedCallback, \Closure|null $removedCallback, bool $strict = true): void {
		$old = is_array($collection) ? $collection : $collection->getArrayCopy();
		$new = is_array($newCollection) ? $newCollection : $newCollection->getArrayCopy();

		if ($strict) {
			$compare = function($a, $b) {
				if ($a === $b) {
					return 0;
				}
				return ($a < $b) ? -1 : 1;
			};

			$addedValues = array_udiff($new, $old, $compare);
			$removedValues = array_udiff($old, $new, $compare);
		} else {
			$addedValues = array_diff($new, $old);
			$removedValues = array_diff($old, $new);
		}

		if ($addedCallback !== null) {
			array_walk($addedValues, $addedCallback);
		}

		if ($removedCallback !== null) {
			array_walk($removedValues, $removedCallback);
		}
	}

	static function contains(array|\ArrayObject &$collection, mixed $value, bool $strict = true): bool {
		 if (is_array($collection)) {
			 return in_array($value, $collection, $strict);
		 }

		foreach ($collection as $key => $object) {
			if (($strict && $value === $object)
					|| (!$strict && $value == $object)) {
				return true;
			}
		}

		return false;
	}

	static function filterNotNull(array $arr): array {
		return array_filter($arr, fn ($v) => $v !== null);
	}

	static function find(iterable $collection, \Closure $callback): mixed {
		foreach ($collection as $value) {
			if ($callback($value)) {
				return $value;
			}
		}
		return null;
	}
}
