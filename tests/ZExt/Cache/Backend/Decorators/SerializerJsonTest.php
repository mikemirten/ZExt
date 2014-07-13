<?php

use ZExt\Cache\Backend\Decorators\SerializerJson;

require_once __DIR__ . '/../BackendTestCase.php';

class SerializerJsonTest extends PHPUnit_Framework_TestCase {
	
	public function testSet() {
		$backend   = new BackendTestCase();
		$decorator = new SerializerJson($backend);
		
		$decorator->set('test1', [10, 20, ['key1', 'key2']]);
		$decorator->set('test2', [30, 40, ['key3', 'key4']]);
		
		$this->assertSame('[10,20,["key1","key2"]]', $backend->get('test1'));
		$this->assertSame('[30,40,["key3","key4"]]', $backend->get('test2'));
	}
	
	public function testSetMany() {
		$backend   = new BackendTestCase();
		$decorator = new SerializerJson($backend);
		
		$decorator->setMany([
			'test1' => [10, 20, ['key1', 'key2']],
			'test2' => [30, 40, ['key3', 'key4']]
		]);
		
		$this->assertSame('[10,20,["key1","key2"]]', $backend->get('test1'));
		$this->assertSame('[30,40,["key3","key4"]]', $backend->get('test2'));
	}
	
	public function testGet() {
		$data = [
			'test1' => '[10,20,["key1","key2"]]',
			'test2' => '[30,40,["key3","key4"]]'
		];
		
		$backend   = new BackendTestCase($data);
		$decorator = new SerializerJson($backend);
		
		$this->assertSame([10, 20, ['key1', 'key2']], $decorator->get('test1'));
		$this->assertSame([30, 40, ['key3', 'key4']], $decorator->get('test2'));
		
		$this->assertNull($decorator->get('test0'));
	}
	
	public function testGetMany() {
		$data = [
			'test1' => '[10,20,["key1","key2"]]',
			'test2' => '[30,40,["key3","key4"]]'
		];
		
		$backend   = new BackendTestCase($data);
		$decorator = new SerializerJson($backend);
		
		$this->assertSame([
			'test1' => [10, 20, ['key1', 'key2']],
			'test2' => [30, 40, ['key3', 'key4']]
		], $decorator->getMany(['test1', 'test2', 'testN']));
	}
	
	public function testHas() {
		$backend   = new BackendTestCase(['test' => 10]);
		$decorator = new SerializerJson($backend);
		
		$this->assertTrue($decorator->has('test'));
		$this->assertFalse($decorator->has('test0'));
	}
	
	public function testRemove() {
		$backend   = new BackendTestCase(['test' => 10]);
		$decorator = new SerializerJson($backend);
		
		$decorator->remove('test');
		
		$this->assertFalse($backend->has('test'));
	}
	
	public function testRemoveMany() {
		$data = [
			'test1' => 10,
			'test2' => 20,
		];
		
		$backend   = new BackendTestCase($data);
		$decorator = new SerializerJson($backend);
		
		$decorator->removeMany(['test1', 'test2']);
		
		$this->assertFalse($backend->has('test1'));
		$this->assertFalse($backend->has('test2'));
	}
	
	public function testIncDec() {
		$data = [
			'test1' => 10,
			'test2' => 20,
		];
		
		$backend   = new BackendTestCase($data);
		$decorator = new SerializerJson($backend);
		
		$decorator->inc('test1');
		$decorator->inc('test2', 10);
		
		$this->assertSame(11, $backend->get('test1'));
		$this->assertSame(30, $backend->get('test2'));
		
		$decorator->dec('test1');
		$decorator->dec('test2', 5);
		
		$this->assertSame(10, $backend->get('test1'));
		$this->assertSame(25, $backend->get('test2'));
	}
	
}