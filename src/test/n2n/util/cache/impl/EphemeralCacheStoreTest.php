<?php
namespace n2n\util\cache\impl;

use PHPUnit\Framework\TestCase;
use n2n\util\cache\CacheItem;

class EphemeralCacheStoreTest extends TestCase {
	private EphemeralCacheStore $ephemeralCacheStore;

	function setUp(): void {
		$this->ephemeralCacheStore = new EphemeralCacheStore();
	}

	function testStore() {
		$name = 'item';
		$characteristics = ['characteristic1' => '1', 'characteristic2' => 'two'];
		$data = 'data';

		$this->ephemeralCacheStore->store($name, $characteristics, $data);
		$cacheItem = $this->ephemeralCacheStore->get($name, $characteristics);

		$this->assertEquals($name, $cacheItem->getName());
		$this->assertEquals($characteristics, $cacheItem->getCharacteristics());
		$this->assertEquals($data, $cacheItem->getData());

		$this->ephemeralCacheStore->store($name, $characteristics, 'otherData');
		$this->assertEquals('otherData', $this->ephemeralCacheStore->get($name, $characteristics)->getData());
	}

	function testGet() {
		$name = 'item';
		$characteristics = ['char1' => '1', 'characteristic2' => 'two'];
		$data = 'data';

		$this->ephemeralCacheStore->store($name, $characteristics, $data);
		$cacheItem = $this->ephemeralCacheStore->get($name, $characteristics);

		$this->assertEquals($name, $cacheItem->getName());
		$this->assertEquals($characteristics, $cacheItem->getCharacteristics());
		$this->assertEquals($data, $cacheItem->getData());

		$characteristics['weather'] = 'cloudy';
		$cacheItem = $this->ephemeralCacheStore->get($name, $characteristics);
		$this->assertEquals(null, $cacheItem);
    }

	function testRemove() {
		$name = 'item';
		$characteristics = ['char1' => '1', 'characteristic2' => 'two'];
		$data = 'data';

		$this->ephemeralCacheStore->store($name, $characteristics, $data);
		$this->ephemeralCacheStore->remove($name, $characteristics);

		$cacheItem = $this->ephemeralCacheStore->get($name, $characteristics);
		$this->assertEquals(null, $cacheItem);
	}

	function testFindAll() {
		$this->ephemeralCacheStore->store('test', ['characteristic1' => '1', 'characteristic2' => 'two', 'characteristic3' => 'asdf'], true);
		$this->ephemeralCacheStore->store('test', ['characteristic1' => '2', 'characteristic2' => 'two', 'characteristic3' => 'asdf'], true);
		$this->ephemeralCacheStore->store('test', ['characteristic1' => '3', 'characteristic2' => 'three', 'characteristic3' => 'yxcv'], true);

		$this->assertCount(3, $this->ephemeralCacheStore->findAll('test'));
		$this->assertCount(2, $this->ephemeralCacheStore->findAll('test', ['characteristic2' => 'two']));
		$this->assertCount(2, $this->ephemeralCacheStore->findAll('test', ['characteristic2' => 'two', 'characteristic3' => 'asdf']));
		$this->assertCount(1, $this->ephemeralCacheStore->findAll('test', ['characteristic1' => '2', 'characteristic2' => 'two', 'characteristic3' => 'asdf']));
		$this->assertCount(0, $this->ephemeralCacheStore->findAll('asdf', []));
		$this->assertCount(0, $this->ephemeralCacheStore->findAll('test', ['new' => 'unknown']));
	}

	function testRemoveAll() {
		$this->ephemeralCacheStore->store('test', ['characteristic1' => '1', 'characteristic2' => 'two', 'characteristic3' => 'asdf'], true);
		$this->ephemeralCacheStore->store('test', ['characteristic1' => '2', 'characteristic2' => 'two', 'characteristic3' => 'asdf'], true);
		$this->ephemeralCacheStore->store('asdf', ['characteristic1' => '3', 'characteristic2' => 'three', 'characteristic3' => 'yxcv'], true);

		$this->ephemeralCacheStore->removeAll('test');

		$this->assertEmpty($this->ephemeralCacheStore->findAll('test'));
		$this->assertNotEmpty($this->ephemeralCacheStore->findAll('asdf'));
	}

	function testClear() {
		$this->ephemeralCacheStore->store('test', ['characteristic1' => '1', 'characteristic2' => 'two', 'characteristic3' => 'asdf'], true);
		$this->ephemeralCacheStore->store('test', ['characteristic1' => '2', 'characteristic2' => 'two', 'characteristic3' => 'asdf'], true);
		$this->ephemeralCacheStore->store('asdf', ['characteristic1' => '3', 'characteristic2' => 'three', 'characteristic3' => 'yxcv'], true);

		$this->ephemeralCacheStore->clear();

		$this->assertEmpty($this->ephemeralCacheStore->findAll('test'));
		$this->assertEmpty($this->ephemeralCacheStore->findAll('asdf'));
	}
}