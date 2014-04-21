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
		}
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
		$client = $this->getCleanClient();
		$client->set('testFlush', 1);
		
		$backend = new MemcacheBackend();
		$backend->flush();
		
		$this->assertFalse($client->get('testFlush'));
	}
	
	public function testSet() {
		$client = $this->getCleanClient();
		
		$backend = new MemcacheBackend();
		
		$this->assertTrue($backend->set('testSet', 1));
		$this->assertEquals(1, $client->get('testSet'));
	}
	
	public function testGet() {
		$client = $this->getCleanClient();
		$client->set('testGet', 2);
		
		$backend = new MemcacheBackend();
		
		$this->assertEquals(2, $backend->get('testGet'));
		$this->assertNull($backend->get('testNot'));
	}
	
	public function testHas() {
		$client = $this->getCleanClient();
		$client->set('testHas', 3);
		
		$backend = new MemcacheBackend();
		
		$this->assertTrue($backend->has('testHas'));
		$this->assertFalse($backend->has('testNot'));
	}
	
	public function testIncDec() {
		$client = $this->getCleanClient();
		$client->set('testInt', 10);
		
		$backend = new MemcacheBackend();
		
		$this->assertEquals(11, $backend->inc('testInt'));
		$this->assertEquals(12, $backend->inc('testInt'));
		
		$this->assertEquals(11, $backend->dec('testInt'));
		$this->assertEquals(10, $backend->dec('testInt'));
		
		$this->assertFalse($backend->inc('testNot'));
		$this->assertFalse($backend->dec('testNot'));
	}
	
	public function testRemove() {
		$client = $this->getCleanClient();
		$client->set('testRemove', 100);
		
		$backend = new MemcacheBackend();
		
		$this->assertTrue($backend->remove('testRemove'));
		$this->assertFalse($client->get('testRemove'));
		
		$this->assertFalse($backend->remove('testNot'));
	}
	
	public function testSetMany() {
		$client = $this->getCleanClient();
		
		$backend = new MemcacheBackend();
		
		$result = $backend->setMany([
			'key1' => 1,
			'key2' => 2
		]);
		
		$this->assertTrue($result);
		
		$this->assertEquals(1, $client->get('key1'));
		$this->assertEquals(2, $client->get('key2'));
	}
	
	public function testGetMany() {
		$client = $this->getCleanClient();
		$client->set('key10', 10);
		$client->set('key11', 11);
		
		$backend = new MemcacheBackend();
		
		$result = $backend->getMany(['key10', 'key11']);
		
		$this->assertEquals(10, $result['key10']);
		$this->assertEquals(11, $result['key11']);
	}
	
	public function testRemoveMany() {
		$client = $this->getCleanClient();
		$client->set('key20', 20);
		$client->set('key21', 21);
		
		$backend = new MemcacheBackend();
		
		$backend->removeMany(['key20', 'key21']);
		
		$this->assertFalse($client->get('key20'));
		$this->assertFalse($client->get('key21'));
	}
	
	public function testNamespace() {
		$client = $this->getCleanClient();
		$client->set('somenamespace_key1', 100);
		
		$backend = new MemcacheBackend();
		$backend->setNamespace('somenamespace');
		
		$this->assertEquals(100, $backend->get('key1'));
		
		$backend->set('key2', 200);
		
		$this->assertEquals(200, $client->get('somenamespace_key2'));
	}
	
	/**
	 * Get the clean Memcache client
	 * 
	 * @return Memcache
	 */
	protected function getCleanClient() {
		if ($this->client === null) {
			$this->client = new Memcache();
			$this->client->connect('127.0.0.1', 11211);
		}
		
		$this->client->flush();
		
		return $this->client;
	}
	
}