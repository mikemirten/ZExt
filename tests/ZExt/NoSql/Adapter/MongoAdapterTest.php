<?php

use ZExt\NoSql\Adapter\MongoAdapter;

class MongoAdapterTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Mongo adapter instance
	 *
	 * @var MongoAdapter
	 */
	protected $mongoAdapter;
	
	/**
	 * Test collection instance
	 *
	 * @var MongoCollection
	 */
	protected $testCollection;
	
	public function setUp() {
		if (! extension_loaded('mongo')) {
			$this->markTestSkipped('The memcache php extension is not loaded');
			return;
		}
		
		$client = new MongoClient('mongodb://127.0.0.1/phpunit');
		
		$this->mongoAdapter = new MongoAdapter($client);
		$this->mongoAdapter->setDBName('phpunit');
		
		$this->testCollection = $client->selectDB('phpunit')->selectCollection('test');
	}
	
	public function tearDown() {
		if ($this->testCollection !== null) {
			$this->testCollection->drop();
		}
	}
	
	public function testConnectionStringManyHostsDefaultPort() {
		$adapter = new MongoAdapter();
		$adapter->setDefaultPort('270');
		$adapter->setDBName('phpunit');
		
		$adapter->addHost('127.0.0.1')
		        ->addHost('127.0.0.2')
				->addHost(['host' => '127.0.0.3']);
		
		$this->assertSame(
			'mongodb://127.0.0.1:270,127.0.0.2:270,127.0.0.3:270/phpunit',
			$adapter->getConnectionString()
		);
	}
	
	public function testConnectionStringDefaultHostManyPorts() {
		$adapter = new MongoAdapter();
		$adapter->setDefaultHost('127.0.0.1');
		$adapter->setDBName('phpunit');
		
		$adapter->addHost(27000);
		$adapter->addHost(27001);
		$adapter->addHost(['port' => 27002]);
		
		$this->assertSame(
			'mongodb://127.0.0.1:27000,127.0.0.1:27001,127.0.0.1:27002/phpunit',
			$adapter->getConnectionString()
		);
	}
	
	public function testConnectionStringAuthHandling() {
		$adapter = new MongoAdapter();
		$adapter->setDefaultPort('270');
		$adapter->setDBName('phpunit');
		
		$adapter->addHost('host1');
		$adapter->addHost('username:password@host2');
		$adapter->addHost('host3');
		
		$this->assertSame(
			'mongodb://username:password@host1:270,host2:270,host3:270/phpunit',
			$adapter->getConnectionString()
		);
	}
	
	/**
	 * @dataProvider testCollectionProvider
	 */
	public function testInsert(array $data) {
		$result = $this->mongoAdapter->insert('test', $data);
		
		$this->assertTrue($result);
		$this->assertEquals($data, $this->testCollection->findOne());
	}
	
	/**
	 * @dataProvider testCollectionProvider
	 */
	public function testUpdate(array $data) {
		$this->testCollection->insert($data);
		
		$result = $this->mongoAdapter->update('test', ['id' => 1], ['name' => 'asdfgh']);
		
		$this->assertTrue($result);
		$this->assertEquals('asdfgh', $this->testCollection->findOne()['name']);
	}
	
	/**
	 * @dataProvider testCollectionProvider
	 */
	public function testFind(array $data) {
		$this->testCollection->insert($data);
		
		$cursor = $this->mongoAdapter->find('test');
		$cursor->rewind();
		
		$this->assertInstanceOf('MongoCursor', $cursor);
		$this->assertEquals($data, $cursor->current());
	}
	
	/**
	 * @dataProvider testCollectionProvider
	 */
	public function testFindFirst(array $data) {
		$this->testCollection->insert($data);
		
		$this->assertEquals($data, $this->mongoAdapter->findFirst('test'));
	}
	
	public function testAggregate() {
		for ($i = 1; $i < 10; ++ $i) {
			$this->testCollection->insert([
				'counter1' => $i,
				'counter2' => $i * $i
			]);
		}
		
		$result = $this->mongoAdapter->aggregate('test',[[
			'$group' => [
				'sum1' => ['$sum' => '$counter1'],
				'sum2' => ['$sum' => '$counter2'],
				'_id'   => null
			]
		]]);
		
		$this->assertSame([
			'_id'  => null,
			'sum1' => 45,
			'sum2' => 285
		], $result[0]);
	}
	
	public function testCollectionProvider() {
		return [[[
			'_id'  => new MongoId('000000000000000000000001'),
			'id'   => 1,
			'name' => 'qwerty'
		]]];
	}
	
}