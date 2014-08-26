<?php

use ZExt\Datagate\DatagateAbstract;

class DatagateAbstractTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Mock of datatate
	 * 
	 * @var DatagateAbstract
	 */
	protected $datagate;
	
	public function setUp() {
		$this->datagate = $this->getMockForAbstractClass('ZExt\Datagate\DatagateAbstract', [], 'TestCaseDatagate');
		$this->datagate->setLocator($this->getMock('ZExt\Di\LocatorInterface'));
		$this->datagate->setPrimaryName('userId');
	}
	
	public function testTableName() {
		$this->assertSame('testCase', $this->datagate->getTableName());
	}
	
	public function testCreateModel() {
		$data = [
			'userId' => 10,
			'name'   => 'John'
		];
		
		$model = $this->datagate->create($data);
		
		$this->assertInstanceOf('ZExt\Model\Model', $model);
		$this->assertInstanceOf('ZExt\Di\LocatorInterface', $model->getLocator());
		$this->assertInstanceOf('TestCaseDatagate', $model->getDatagate());
	}
	
	public function testCreateCollection() {
		$data = [
			[
				'userId' => 10,
				'name'   => 'John'
			],
			[
				'userId' => 20,
				'name'   => 'Steve'
			]
		];
		
		$collection = $this->datagate->createCollection($data);
		
		$this->assertInstanceOf('ZExt\Model\Collection', $collection);
		$this->assertInstanceOf('ZExt\Di\LocatorInterface', $collection->getLocator());
		$this->assertInstanceOf('TestCaseDatagate', $collection->getDatagate());
		$this->assertSame('userId', $collection->getPrimary());
		$this->assertSame('ZExt\Model\Model', $collection->getModel());
	}
	
	public function testCreateCollectionWithoutId() {
		$data = [
			['name' => 'John'],
			['name' => 'Steve']
		];
		
		$collection = $this->datagate->createCollection($data);
		
		$this->assertInstanceOf('ZExt\Model\Collection', $collection);
		$this->assertNull($collection->getPrimary());
	}
	
}