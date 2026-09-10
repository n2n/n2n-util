<?php

namespace n2n\util\col;

/**
 * @template K
 * @template V
 * @implements \Iterator<K, V>
 */
class SplObjectStorageIterator implements \Iterator {

	function __construct(private \SplObjectStorage $storage) {

	}

	/**
	 * @return V
	 */
	public function current(): mixed {
		return $this->storage->getInfo();
	}

	public function next(): void {
		$this->storage->next();
	}

	/**
	 * @return K
	 */
	public function key(): object {
		return $this->storage->current();
	}

	public function valid(): bool {
		return $this->storage->valid();
	}

	public function rewind(): void {
		$this->storage->rewind();
	}
}