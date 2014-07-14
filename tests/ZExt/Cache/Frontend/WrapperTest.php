<?php

use ZExt\Cache\Frontend\Wrapper;

require_once __DIR__ . '/../Backend/BackendTestCase.php';

class WrapperTest extends PHPUnit_Framework_TestCase {
	
	public function testSet() {
		$backend  = new BackendTestCase();
		$frontend = new Wrapper($backend, 'nmspace');
		
		$frontend->set('test1', 10);
		$frontend->set('test2', 20);
		
		$this->assertSame(10, $backend->get('nmspace_test1'));
		$this->assertSame(20, $backend->get('nmspace_test2'));
	}
	
	public function testSetWithTags() {
		$backend  = new BackendTestCase();
		$frontend = new Wrapper($backend, 'nmspace');
		
		$frontend->set('test', 10, 0, 'tag');
		
		$this->assertSame(
			'nmspace_tag',
			$backend->getLastTagsArgument()
		);
		
		$frontend->set('test', 10, 0, ['tag1', 'tag2']);
		
		$this->assertSame(
			['nmspace_tag1', 'nmspace_tag2'],
			$backend->getLastTagsArgument()
		);
	}
	
	public function testSetMany() {
		$backend  = new BackendTestCase();
		$frontend = new Wrapper($backend, 'nmspace');
		
		$frontend->setMany([
			'test1' => 10,
			'test2' => 20
		]);
		
		$this->assertSame(10, $backend->get('nmspace_test1'));
		$this->assertSame(20, $backend->get('nmspace_test2'));
	}
	
	public function testSetManyWithTags() {
		$backend  = new BackendTestCase();
		$frontend = new Wrapper($backend, 'nmspace');
		
		$frontend->setMany(['test1' => 10], 0, 'tag');
		
		$this->assertSame(
			'nmspace_tag',
			$backend->getLastTagsArgument()
		);
		
		$frontend->setMany(['test1' => 10], 0, ['tag1', 'tag2']);
		
		$this->assertSame(
			['nmspace_tag1', 'nmspace_tag2'],
			$backend->getLastTagsArgument()
		);
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testGet($data) {
		$backend  = new BackendTestCase($data);
		$frontend = new Wrapper($backend, 'nmspace');
		
		$this->assertSame(10, $frontend->get('test1'));
		$this->assertSame(20, $frontend->get('test2'));
		
		$this->assertNull($frontend->get('testN'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testGetMany($data) {
		$backend  = new BackendTestCase($data);
		$frontend = new Wrapper($backend, 'nmspace');
		
		$this->assertSame([
			'test1' => 10,
			'test2' => 20
		], $frontend->getMany(['test1', 'test2']));
	}
	
	public function testGetByTag() {
		$backend  = new BackendTestCase();
		$frontend = new Wrapper($backend, 'nmspace');
		
		$frontend->getByTag('tag');
		
		$this->assertSame(
			'nmspace_tag',
			$backend->getLastTagsArgument()
		);
		
		$frontend->getByTag(['tag1', 'tag2']);
		
		$this->assertSame(
			['nmspace_tag1', 'nmspace_tag2'],
			$backend->getLastTagsArgument()
		);
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testHas($data) {
		$backend  = new BackendTestCase($data);
		$frontend = new Wrapper($backend, 'nmspace');
		
		$this->assertTrue($frontend->has('test1'));
		$this->assertTrue($frontend->has('test2'));
		$this->assertFalse($frontend->has('testN'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testRemove($data) {
		$backend  = new BackendTestCase($data);
		$frontend = new Wrapper($backend, 'nmspace');
		
		$frontend->remove('test1');
		
		$this->assertFalse($backend->has('nmspace_test1'));
		$this->assertTrue($backend->has('nmspace_test2'));
		
		$frontend->remove('test2');
		
		$this->assertFalse($backend->has('nmspace_test2'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testRemoveMany($data) {
		$backend  = new BackendTestCase($data);
		$frontend = new Wrapper($backend, 'nmspace');
		
		$frontend->removeMany(['test1', 'test2']);
		
		$this->assertFalse($backend->has('nmspace_test1'));
		$this->assertFalse($backend->has('nmspace_test2'));
		$this->assertTrue($backend->has('nmspace_test3'));
	}
	
	public function testRemoveByTag() {
		$backend  = new BackendTestCase();
		$frontend = new Wrapper($backend, 'nmspace');
		
		$frontend->removeByTag('tag');
		
		$this->assertSame(
			'nmspace_tag',
			$backend->getLastTagsArgument()
		);
		
		$frontend->removeByTag(['tag1', 'tag2']);
		
		$this->assertSame(
			['nmspace_tag1', 'nmspace_tag2'],
			$backend->getLastTagsArgument()
		);
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testIncDec($data) {
		$backend  = new BackendTestCase($data);
		$frontend = new Wrapper($backend, 'nmspace');
		
		$frontend->inc('test1');
		$frontend->inc('test2', 5);
		
		$this->assertSame(11, $backend->get('nmspace_test1'));
		$this->assertSame(25, $backend->get('nmspace_test2'));
		
		$frontend->dec('test1');
		$frontend->dec('test2', 5);
		
		$this->assertSame(10, $backend->get('nmspace_test1'));
		$this->assertSame(20, $backend->get('nmspace_test2'));
	}
	
	public function cacheSourceProvider() {
		return [
			[
				[
					'nmspace_test1' => 10,
					'nmspace_test2' => 20,
					'nmspace_test3' => 30,
				]
			]
		];
	}
	
}