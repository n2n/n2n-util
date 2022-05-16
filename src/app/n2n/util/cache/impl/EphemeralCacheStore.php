<?php

namespace n2n\util\cache\impl;

use n2n\util\cache\CacheItem;
use n2n\util\cache\CacheStore;
use n2n\util\cache\CorruptedCacheStoreException;
use n2n\util\io\IoUtils;

class EphemeralCacheStore implements CacheStore {
	/**
	 * 
	 * @var CacheItem[][] $cacheItems
	 */
    private $cacheItems = [];

    public function store(string $name, array $characteristics, mixed $data, \DateTime $lastMod = null): void {
		$this->remove($name, $characteristics);
		$this->nsStore($name)[] = new CacheItem($name, $characteristics, $data);
    }

    public function get(string $name, array $characteristics): ?CacheItem {
		foreach ($this->nsStore($name) as $cacheItem) {
			if ($cacheItem->matchesCharacteristics($characteristics)) {
				return $cacheItem;
			}
		}

		return null;
    }

    public function remove(string $name, array $characteristics): void {
        foreach ($this->nsStore($name) as $i => $cacheItem) {
			if ($cacheItem->matchesCharacteristics($characteristics)) {
				unset($this->cacheItems[$name][$i]);
			}
		}
    }

	/**
	 * @param string $name
	 * @param array|null $characteristicNeedles
	 * @return CacheItem[]
	 */
    public function findAll(string $name, array $characteristicNeedles = null): array {
        $found = [];
		$cacheItems = $this->nsStore($name);

		if (null === $characteristicNeedles) {
			return $cacheItems;
		}

		foreach ($cacheItems as $cacheItem) {
			if (!$cacheItem->containsCharacteristics($characteristicNeedles)) {
				continue;
			}

			$found[] = $cacheItem;
		}

		return $found;
    }

    public function removeAll(string $name, array $characteristicNeedles = null): void {
		if ($characteristicNeedles === null) {
			unset($this->cacheItems[$name]);
			return;
		}

		foreach ($this->nsStore($name) as $i => $cacheItem) {
			if ($cacheItem->containsCharacteristics($characteristicNeedles)) {
				unset($this->cacheItems[$name][$i]);
			}
		}
    }

    public function clear(): void {
        $this->cacheItems = [];
    }

	private function &nsStore(string $namespace): array {
		if (!isset($this->cacheItems[$namespace])) {
			$this->cacheItems[$namespace] = [];
		}

		return $this->cacheItems[$namespace];
	}
}