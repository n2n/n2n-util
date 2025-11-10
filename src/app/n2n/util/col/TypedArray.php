<?php

namespace n2n\util\col;

use n2n\util\type\TypeConstraint;
use SplObjectStorage;
use n2n\util\type\TypeName;
use n2n\util\type\NamedTypeConstraint;
use n2n\util\type\TypeUtils;
use Traversable;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use InvalidArgumentException;
use OutOfBoundsException;
use ArrayIterator;
use n2n\util\StringUtils;


/**
 * Example impl
 *
 * ```php
 * / **
 *  * @extends TypedArray<scalar, \DateTime>
 *  * /
 * #[ValueType(\DateTime::class)]
 * class DataTimes extends TypedArray {
 *
 * }
 * ```
 *
 * @template K
 * @template V
 * @template-implements \ArrayAccess<K, V>
 * @template-implements Collection<K, V>
 */
abstract class TypedArray implements \ArrayAccess, Collection {

	private ?array $array = null;
	private ?SplObjectStorage $objectStorage = null;
	private NamedTypeConstraint $keyTypeConstraint;
	private NamedTypeConstraint $valueTypeConstraint;

	/**
	 * @param V[]|\IteratorAggregate<K, V> $array
	 */
	final function __construct(array|\IteratorAggregate $array = []) {
		$class = new \ReflectionClass($this);
		$this->keyTypeConstraint = CollectionTypeUtils::detectKeyTypeConstraint($class);
		$this->valueTypeConstraint = CollectionTypeUtils::detectValueTypeConstraint($class);

		if (TypeName::isScalar($this->keyTypeConstraint->getTypeName())) {
			$this->array = [];
		} else {
			$this->objectStorage = new SplObjectStorage();
		}

		foreach ($array as $key => $value) {
			$this->offsetSet($key, $value);
		}
	}

	final function getIterator(): Traversable {
		if ($this->objectStorage !== null) {
			return new SplObjectStorageIterator($this->objectStorage);
		}

		return new ArrayIterator($this->array);
	}

	/**
	 * @param K $offset
	 * @return bool
	 */
	final function offsetExists(mixed $offset): bool {
		if ($this->objectStorage !== null) {
			return $this->objectStorage->offsetExists($offset);
		}

		return array_key_exists($offset, $this->array);
	}

	/**
	 * @param K $offset
	 * @return V
	 */
	final function offsetGet(mixed $offset): mixed {
		$offset = $this->valKey($offset);

		if (($this->objectStorage !== null && !$this->objectStorage?->offsetExists($offset))
				|| ($this->array !== null && !array_key_exists($offset, $this->array))) {
			throw new OutOfBoundsException('Key "'. TypeUtils::prettyValue($offset) . '" does not exist.');
		}

		if ($this->objectStorage !== null) {
			return $this->objectStorage->offsetGet($offset);
		}

		return $this->array[$offset];
	}

	private function valKey(mixed $key): mixed {
		try {
			return $this->keyTypeConstraint->validate($key);
		} catch (ValueIncompatibleWithConstraintsException $e) {
			throw new InvalidArgumentException('Passed array key is invalid: ' . TypeUtils::prettyValue($key),
					previous: $e);
		}
	}

	private function valValue(mixed $value): mixed {
		try {
			return $this->valueTypeConstraint->validate($value);
		} catch (ValueIncompatibleWithConstraintsException $e) {
			throw new InvalidArgumentException('Passed array value is invalid: ' . TypeUtils::prettyValue($value),
					previous: $e);
		}
	}

	/**
	 * @param K $offset
	 * @param V $value
	 * @return void
	 */
	final function offsetSet(mixed $offset, mixed $value): void {
		$offset = $this->valKey($offset);
		$value = $this->valValue($value);

		if ($this->objectStorage !== null) {
			$this->objectStorage->offsetSet($offset, $value);
			return;
		}

		$this->array[$offset] = $value;
	}

	/**
	 * @param K $offset
	 * @return void
	 */
	final function offsetUnset(mixed $offset): void {
		if ($this->objectStorage !== null) {
			$this->objectStorage->offsetUnset($offset);
			return;
		}

		unset($this->array[$offset]);
	}

	final function count(): int {
		return $this->objectStorage?->count() ?? count($this->array);
	}

	final function isEmpty(): bool {
		if ($this->objectStorage !== null) {
			return $this->objectStorage->count() === 0;
		}

		return empty($this->array);
	}

	final function clear(): void {
		if ($this->objectStorage !== null) {
			$this->objectStorage = new SplObjectStorage();
		} else {
			$this->array = [];
		}
	}

	final function toArray(): array {
		if ($this->array !== null) {
			return $this->array;
		}

		$array = [];
		foreach ($this as $key => $value) {
			$array[StringUtils::strOf($key)] = $value;
		}
		return $array;
	}

	final function values(): array {
		if ($this->array !== null) {
			return array_values($this->array);
		}

		$array = [];
		foreach ($this as $value) {
			$array[] = $value;
		}
		return $array;
	}

	final function keys(): array {
		if ($this->array !== null) {
			return array_keys($this->array);
		}

		return $this->objectStorage->toArray();
	}
}

