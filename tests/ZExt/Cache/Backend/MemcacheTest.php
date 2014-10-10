<?php

use ZExt\Cache\Backend\Memcache as MemcacheBackend;

class MemcacheTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Memcache native PHP extention client
	 *
	 * @var Memcache 
	 */
	protected $client;
	
	public function setUp() {
		if (! extension_loaded('memcache')) {
			$this->markTestSkipped('The memcache php extension is not loaded');
			return;
		}
		
		$this->client = new Memcache();
		$this->client->connect('127.0.0.1', 11211);
		$this->client->flush();
	}
	
	public function testInitParams() {
		$backend = new MemcacheBackend();
		$backend->addServer('127.0.0.1', 11211);
		
		$client = $backend->getClient();
		
		$this->assertArrayHasKey('127.0.0.1:11211', $client->getExtendedStats());
	}
	
	public function testConstructor() {
		$backend = new MemcacheBackend([
			'namespace'           => 'nmsp12',
			'compression'         => true,
			'operationExceptions' => false
		]);
		
		$this->assertEquals('nmsp12', $backend->getNamespace());
		$this->assertTrue($backend->getCompression());
		$this->assertFalse($backend->getOperationExceptions());
	}
	
	public function testFlush() {
		$this->client->set('testFlush', 1);
		
		$backend = new MemcacheBackend();
		$backend->flush();
		
		$this->assertFalse($this->client->get('testFlush'));
	}
	
	public function testSet() {
		$backend = new MemcacheBackend();
		
		$this->assertTrue($backend->set('testSet', 1));
		$this->assertEquals(1, $this->client->get('testSet'));
	}
	
	public function testGet() {
		$this->client->set('testGet', 2);
		
		$backend = new MemcacheBackend();
		
		$this->assertEquals(2, $backend->get('testGet'));
		$this->assertNull($backend->get('testNot'));
	}
	
	public function testHas() {
		$this->client->set('testHas', 3);
		
		$backend = new MemcacheBackend();
		
		$this->assertTrue($backend->has('testHas'));
		$this->assertFalse($backend->has('testNot'));
	}
	
	public function testIncDec() {
		$this->client->set('testInt', 10);
		
		$backend = new MemcacheBackend();
		
		$this->assertEquals(11, $backend->inc('testInt'));
		$this->assertEquals(12, $backend->inc('testInt'));
		
		$this->assertEquals(11, $backend->dec('testInt'));
		$this->assertEquals(10, $backend->dec('testInt'));
		
		$this->assertFalse($backend->inc('testNot'));
		$this->assertFalse($backend->dec('testNot'));
	}
	
	public function testRemove() {
		$this->client->set('testRemove', 100);
		
		$backend = new MemcacheBackend();
		
		$this->assertTrue($backend->remove('testRemove'));
		$this->assertFalse($this->client->get('testRemove'));
		
		$this->assertFalse($backend->remove('testNot'));
	}
	
	public function testSetMany() {
		$backend = new MemcacheBackend();
		
		$result = $backend->setMany([
			'key1' => 1,
			'key2' => 2
		]);
		
		$this->assertTrue($result);
		
		$this->assertEquals(1, $this->client->get('key1'));
		$this->assertEquals(2, $this->client->get('key2'));
	}
	
	public function testGetMany() {
		$this->client->set('key10', 10);
		$this->client->set('key11', 11);
		
		$backend = new MemcacheBackend();
		
		$result = $backend->getMany(['key10', 'key11']);
		
		$this->assertEquals(10, $result['key10']);
		$this->assertEquals(11, $result['key11']);
	}
	
	public function testRemoveMany() {
		$this->client->set('key20', 20);
		$this->client->set('key21', 21);
		
		$backend = new MemcacheBackend();
		
		$backend->removeMany(['key20', 'key21']);
		
		$this->assertFalse($this->client->get('key20'));
		$this->assertFalse($this->client->get('key21'));
	}
	
	public function testNamespace() {
		$this->client->set('somenamespace_key1', 100);
		
		$backend = new MemcacheBackend();
		$backend->setNamespace('somenamespace');
		
		$this->assertEquals(100, $backend->get('key1'));
		
		$backend->set('key2', 200);
		
		$this->assertEquals(200, $this->client->get('somenamespace_key2'));
	}
	
}