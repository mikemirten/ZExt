<?php

use ZExt\Datagate\MongoCollection;

class MongoCollectionTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Mongo collection datagate
	 *
	 * @var MongoCollection 
	 */
	protected $mongoCollection;
	
	/**
	 * Mock of Mongo adapter
	 *
	 * @var ZExt\NoSql\Adapter\MongoAdapter 
	 */
	protected $mongoAdapter;
	
	/**
	 * @dataProvider dbdataProvider
	 */
	public function setUp() {
		if (! extension_loaded('mongo')) {
			$this->markTestSkipped('The mongodb php extension is not loaded');
			return;
		}
		
		$this->mongoAdapter = $this->getMock('ZExt\NoSql\Adapter\MongoAdapter');
		
		$this->mongoCollection = new MongoCollection($this->mongoAdapter);
		$this->mongoCollection->setTableName('users');
		
		$this->mongoCollection->setResultType(
				MongoCollection::RESULTSET_ARRAY
			  | MongoCollection::RESULT_ARRAY
		);
	}
	
	public function testGetAdapter() {
		$adapter = $this->mongoCollection->getAdapter();
		
		$this->assertInstanceOf('ZExt\NoSql\Adapter\MongoAdapter', $adapter);
	}
	
	/**
	 * @dataProvider dbdataProvider
	 */
	public function testFind(array $data, array $ids) {
		$this->mongoAdapter
			->expects($this->any())
			->method('find')
			->with(
				$this->equalTo('users'),
				$this->equalTo(['_id' => ['$in' => $ids]])
			)
			->will($this->returnValue(new ArrayIterator($data)));
		
		$result = $this->mongoCollection->find(['_id' => ['$in' => $ids]]);
		
		$this->assertSame($data, $result);
	}
	
	/**
	 * @dataProvider dbdataProvider
	 */
	public function testFindFirst(array $data, array $ids) {
		$this->mongoAdapter
			->expects($this->any())
			->method('findFirst')
			->with(
				$this->equalTo('users'),
				$this->equalTo(['_id' => $ids[0]])
			)
			->will($this->returnValue($data[0]));
		
		$result = $this->mongoCollection->findFirst(['_id' => $ids[0]]);
		
		$this->assertSame($data[0], $result);
	}
	
	/**
	 * @dataProvider dbdataProvider
	 */
	public function testFindByPrimaryIdOne(array $data, array $ids) {
		$this->mongoAdapter
			->expects($this->any())
			->method('findFirst')
			->with(
				$this->equalTo('users'),
				$this->equalTo(['_id' => $ids[0]])
			)
			->will($this->returnValue($data[0]));
		
		$result = $this->mongoCollection->findByPrimaryId($ids[0]);
		
		$this->assertSame($data[0], $result);
	}
	
	/**
	 * @dataProvider dbdataProvider
	 */
	public function testFindByPrimaryIdMany(array $data, array $ids) {
		$this->mongoAdapter
			->expects($this->any())
			->method('find')
			->with(
				$this->equalTo('users'),
				$this->equalTo(['_id' => ['$in' => $ids]])
			)
			->will($this->returnValue(new ArrayIterator($data)));
		
		$result = $this->mongoCollection->findByPrimaryId($ids);
		
		$this->assertSame($data, $result);
	}
	
	/**
	 * @dataProvider dbdataProvider
	 */
	public function testGetIterator(array $data, array $ids) {
		$this->mongoAdapter
			->expects($this->any())
			->method('find')
			->with(
				$this->equalTo('users'),
				$this->equalTo(['_id' => ['$in' => $ids]])
			)
			->will($this->returnValue(new ArrayIterator($data)));
		
		$result = $this->mongoCollection->getIterator(['_id' => ['$in' => $ids]]);
		
		$this->assertInstanceOf('ZExt\Model\Iterator', $result);
		$this->assertCount(3, $result);
		$this->assertSame($data, $result->toArray(true));
	}
	
	public function testAggregate() {
		$this->mongoAdapter
			->expects($this->any())
			->method('aggregate')
			->with(
				$this->equalTo('users'),
				$this->equalTo([[
					'$group' => [
						'summ' => ['$sum' => '$id'],
						'_id'   => null
					]
				]])
			)
			->will($this->returnValue(new ArrayIterator([[
				'_id'  => null,
				'summ' => 20
			]])));
		
		$result = $this->mongoCollection->aggregate([[
			'$group' => [
				'summ' => ['$sum' => '$id'],
				'_id'   => null
			]
		]]);
		
		$this->assertSame(['summ' => 20], $result);
	}
	
	/**
	 * @dataProvider dbdataProvider
	 */
	public function testInsert(array $data) {
		$this->mongoAdapter
			->expects($this->any())
			->method('insert')
			->with(
				$this->equalTo('users'),
				$this->equalTo($data[0])
			)
			->will($this->returnValue(true));
		
		$result = $this->mongoCollection->insert($data[0]);
		
		$this->assertTrue($result);
	}
	
	public function testDelete() {
		$this->mongoAdapter
			->expects($this->any())
			->method('remove')
			->with(
				$this->equalTo('users'),
				$this->equalTo(['id' => 1])
			)
			->will($this->returnValue(true));
		
		$result = $this->mongoCollection->delete(['id' => 1]);
		
		$this->assertTrue($result);
	}
	
	/**
	 * @dataProvider dbdataProvider
	 */
	public function testSaveModelInsert(array $data) {
		$model = $this->mongoCollection->create($data[0]);
		unset($model->_id);
		
		$this->mongoAdapter
			->expects($this->any())
			->method('insert')
			->with(
				$this->equalTo('users'),
				$this->equalTo($data[0])
			)
			->will($this->returnValue(true));
		
		$this->assertTrue($model->save());
	}
	
	/**
	 * @dataProvider dbdataProvider
	 */
	public function testSaveModelUpdate(array $data, array $ids) {
		$model = $this->mongoCollection->create($data[0]);
		
		$model->name = 'abc';
		
		$this->mongoAdapter
			->expects($this->any())
			->method('update')
			->with(
				$this->equalTo('users'),
				$this->equalTo(['_id' => $ids[0]]),
				$this->equalTo(['$set' => ['name' => 'abc']])
			)
			->will($this->returnValue(true));
		
		$this->assertTrue($model->save());
	}
	
	/**
	 * @dataProvider dbdataProvider
	 */
	public function testSaveCollectionInsert(array $data) {
		$collection = $this->mongoCollection->createCollection($data);
		unset($collection->_id);
		
		foreach ($data as $key => $value) {
			$this->mongoAdapter
				->expects($this->at($key))
				->method('insert')
				->with(
					$this->equalTo('users'),
					$this->equalTo($value)
				)
				->will($this->returnValue(true));
		}
		
		$this->assertTrue($collection->save());
	}
	
	/**
	 * @dataProvider dbdataProvider
	 */
	public function testSaveCollectionUpdate(array $data) {
		$collection = $this->mongoCollection->createCollection($data);
		
		foreach ($collection as $model) {
			$model->options = 0;
		}
		
		foreach ($data as $key => $value) {
			$this->mongoAdapter
				->expects($this->at($key))
				->method('update')
				->with(
					$this->equalTo('users'),
					$this->equalTo(['_id' => $value['_id']]),
					$this->equalTo(['$set' => ['options' => 0]])
				)
				->will($this->returnValue(true));
		}
		
		$this->assertTrue($collection->save());
	}
	
	/**
	 * @dataProvider dbdataProvider
	 */
	public function testRemoveModel(array $data, array $ids) {
		$model = $this->mongoCollection->create($data[0]);
		
		$this->mongoAdapter
			->expects($this->any())
			->method('remove')
			->with(
				$this->equalTo('users'),
				$this->equalTo(['_id' => $ids[0]])
			)
			->will($this->returnValue(true));
		
		$this->assertTrue($model->remove());
	}
	
	/**
	 * @dataProvider dbdataProvider
	 */
	public function testRemoveCollection(array $data, array $ids) {
		$collection = $this->mongoCollection->createCollection($data);
		
		$this->mongoAdapter
			->expects($this->any())
			->method('remove')
			->with(
				$this->equalTo('users'),
				$this->equalTo(['_id' => ['$in' => $ids]])
			)
			->will($this->returnValue(true));
		
		$this->assertTrue($collection->remove());
	}
	
	public function testQuery() {
		$query = $this->mongoCollection->query();
		
		$this->assertInstanceOf('ZExt\Datagate\Criteria\MongoCriteria', $query);
	}
	
	public function dbdataProvider() {
		return [[
			[
				['_id' => '000000000000000000000001', 'id' => 1, 'name' => 'qwerty'],
				['_id' => '000000000000000000000002', 'id' => 2, 'name' => 'asdfgh'],
				['_id' => '000000000000000000000003', 'id' => 3, 'name' => 'zxcvbn']
			],
			[
				'000000000000000000000001',
				'000000000000000000000002',
				'000000000000000000000003',
			]
		]];
	}
	
}