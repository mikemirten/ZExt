<?php

use ZExt\Datagate\Criteria\MongoCriteria as Criteria;

require_once __DIR__ . '/../MongoTestDatagate.php';

class CriteriaTest extends PHPUnit_Framework_TestCase {
	
	public function testWhereEqual() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId = ?', 200);
		
		$this->assertEquals([
			'postId' => 200
		], $criteria->assemble());
		
		$criteria->where('active = ?', true);
		$criteria->where('role = admin');
		$criteria->where('test = 1.5');
		
		$this->assertEquals([
			'postId' => 200,
			'active' => true,
			'role'   => 'admin',
			'test'   => 1.5
		], $criteria->assemble());
	}
	
	public function testWhereNotEqual() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId != ?', 200);
		
		$this->assertEquals([
			'postId' => ['$ne' => 200]
		], $criteria->assemble());
		
		$criteria->where('active != ?', true);
		$criteria->where('role != admin');
		
		$this->assertEquals([
			'postId' => ['$ne' => 200],
			'active' => ['$ne' => true],
			'role'   => ['$ne' => 'admin'],
		], $criteria->assemble());
	}
	
	public function testWhereIn() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId in(?)', [1, 2, 3, 4]);
		
		$this->assertEquals([
			'postId' => ['$in' => [1, 2, 3, 4]]
		], $criteria->assemble());
		
		$criteria->where('role in(?)', ['guest', 'admin']);
		
		$this->assertEquals([
			'postId' => ['$in' => [1, 2, 3, 4]],
			'role'   => ['$in' => ['guest', 'admin']]
		], $criteria->assemble());
	}
	
	public function testWhereNotIn() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId not in(?)', [1, 2, 3, 4]);
		
		$this->assertEquals([
			'postId' => ['$nin' => [1, 2, 3, 4]]
		], $criteria->assemble());
		
		$criteria->where('role not in(?)', ['guest', 'admin']);
		
		$this->assertEquals([
			'postId' => ['$nin' => [1, 2, 3, 4]],
			'role'   => ['$nin' => ['guest', 'admin']]
		], $criteria->assemble());
	}
	
	public function testWhereLessThan() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId < ?', 100);
		$criteria->where('rate < 10');
		
		$this->assertEquals([
			'postId' => ['$lt' => 100],
			'rate'   => ['$lt' => 10]
		], $criteria->assemble());
		
		$criteria->where('userId <= 200', 200);
		$criteria->where('status <= 100');
		
		$this->assertEquals([
			'postId' => ['$lt'  => 100],
			'userId' => ['$lte' => 200],
			'rate'   => ['$lt'  => 10],
			'status' => ['$lte' => 100],
		], $criteria->assemble());
	}
	
	public function testWhereMoreThan() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId > ?', 100);
		$criteria->where('rate > 10');
		
		$this->assertEquals([
			'postId' => ['$gt' => 100],
			'rate'   => ['$gt' => 10]
		], $criteria->assemble());
		
		$criteria->where('userId >= 200', 200);
		$criteria->where('status >= 100');
		
		$this->assertEquals([
			'postId' => ['$gt'  => 100],
			'userId' => ['$gte' => 200],
			'rate'   => ['$gt'  => 10],
			'status' => ['$gte' => 100],
		], $criteria->assemble());
	}
	
	public function testWhereInArray() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId in array(?)', [1, 2, 3, 4]);
		
		$this->assertEquals([
			'postId' => ['$all' => [1, 2, 3, 4]]
		], $criteria->assemble());
	}
	
	public function testWhereExists() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId exists(?)', true);
		
		$this->assertEquals([
			'postId' => ['$exists' => true]
		], $criteria->assemble());
		
		$criteria->where('userId exists(?)', false);
		
		$this->assertEquals([
			'postId' => ['$exists' => true],
			'userId' => ['$exists' => false]
		], $criteria->assemble());
	}
	
	public function testWhereType() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId type(?)', Criteria::MONGO_TYPE_INT32);
		
		$this->assertEquals([
			'postId' => ['$type' => 16]
		], $criteria->assemble());
	}
	
	public function testWhereIsArray() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('roles is array()');
		
		$this->assertEquals([
			'roles'   => ['$type' => 4]
		], $criteria->assemble());
	}
	
	public function testWhereIsInt() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('role is int()');
		
		$this->assertEquals([
			'role'   => ['$type' => 16]
		], $criteria->assemble());
	}
	
	public function testWhereIsString() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('role is string()');
		
		$this->assertEquals([
			'role'   => ['$type' => 2]
		], $criteria->assemble());
	}
	
	public function testWhereIsBool() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('enabled is bool()');
		
		$this->assertEquals([
			'enabled'   => ['$type' => 8]
		], $criteria->assemble());
	}
	
	public function testWhereIsNull() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('enabled is null()');
		
		$this->assertEquals([
			'enabled'   => ['$type' => 10]
		], $criteria->assemble());
	}
	
	public function testWhereRegexp() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId regexp(?)', '/^[a-z]+$/i');
		$query = $criteria->assemble();
		
		$this->assertInstanceOf('MongoRegex', $query['postId']);
		$this->assertEquals('^[a-z]+$', $query['postId']->regex);
		$this->assertEquals('i', $query['postId']->flags);
		
		$criteria = $datagate->query();
		
		$criteria->where('postId regexp(/^([a-z]+)$/i)');
		$query = $criteria->assemble();
		
		$this->assertInstanceOf('MongoRegex', $query['postId']);
		$this->assertEquals('^([a-z]+)$', $query['postId']->regex);
		$this->assertEquals('i', $query['postId']->flags);
	}
	
	public function testWhereLike() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('title like(?)', '%qwerty');
		$query = $criteria->assemble();
		
		$this->assertInstanceOf('MongoRegex', $query['title']);
		$this->assertEquals('qwerty$', $query['title']->regex);
		$this->assertEquals('', $query['title']->flags);
		
		$criteria = $datagate->query();
		
		$criteria->where('title like qwerty%');
		$query = $criteria->assemble();
		
		$this->assertInstanceOf('MongoRegex', $query['title']);
		$this->assertEquals('^qwerty', $query['title']->regex);
		$this->assertEquals('', $query['title']->flags);
	}
	
	public function testWhereArraySize() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('roles array size(?)', 10);
		$criteria->where('friends array size(5)');
		
		$this->assertEquals([
			'roles'   => ['$size' => 10],
			'friends' => ['$size' => 5]
		], $criteria->assemble());
		
		$criteria = $datagate->query();
		
		$criteria->where('roles array count(?)', 20);
		$criteria->where('friends array count(10)');
		
		$this->assertEquals([
			'roles'   => ['$size' => 20],
			'friends' => ['$size' => 10]
		], $criteria->assemble());
	}
	
	public function testWhereOrEqual() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('role = admin || role = moderator');
		$criteria->where('postId = ?', 100);
		
		$this->assertEquals([
			'$or' => [
				['role' => 'admin'],
				['role' => 'moderator']
			],
			'postId' => 100
		], $criteria->assemble());
	}
	
	public function testWhereMultipleOrEqual() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('role = admin || role = moderator');
		$criteria->where('postId = 1 || postId = 2');
		
		$this->assertEquals(['$and' => [
			['$or' => [
				['role' => 'admin'],
				['role' => 'moderator']
			]],
			['$or' => [
				['postId' => 1],
				['postId' => 2]
			]],
		]], $criteria->assemble());
	}
	
	public function testWhereOrLessThan() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('roleId < 10 || roleId <= 50');
		$criteria->where('postId = ?', 100);
		
		$this->assertEquals([
			'$or' => [
				['roleId' => ['$lt'  => 10]],
				['roleId' => ['$lte' => 50]]
			],
			'postId' => 100
		], $criteria->assemble());
	}
	
	public function testWhereOrIsType() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('role is int() || role is string()');
		
		$this->assertEquals([
			'$or' => [
				['role' => ['$type' => 16]],
				['role' => ['$type' => 2]]
			]
		], $criteria->assemble());
	}
	
	public function testWhereMultipleOrComplex() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId in(?) || postId = 0', [1, 2, 3, 4]);
		$criteria->where('userId not in(?) || role != admin', [10, 20, 30, 40]);
		
		$this->assertEquals(['$and' => [
			['$or' => [
				['postId' => ['$in' => [1, 2, 3, 4]]],
				['postId' => 0]
			]],
			['$or' => [
				['userId' => ['$nin' => [10, 20, 30, 40]]],
				['role'   => ['$ne'  => 'admin']]
			]],
		]], $criteria->assemble());
	}
	
	public function testLimit() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->limit(10);
		$this->assertEquals(10, $criteria->getLimit());
		
		$criteria->limit(20, 40);
		$this->assertEquals(20, $criteria->getLimit());
		$this->assertEquals(40, $criteria->getOffset());
	}
	
	public function testOffset() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->offset(10);
		$this->assertEquals(10, $criteria->getOffset());
	}
	
	public function testSort() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->sort('time');
		$this->assertEquals([
			'time' => 1
		], $criteria->getSortConditions());
		
		$criteria->sort('postId ASC');
		$this->assertEquals([
			'time'   => 1,
			'postId' => 1
		], $criteria->getSortConditions());
	}
	
	public function testSortDesc() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->sort('time DESC');
		$this->assertEquals([
			'time' => -1
		], $criteria->getSortConditions());
	}
	
	public function testSortMultiple() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->sort('time ASC, postId DESC');
		$this->assertEquals([
			'time'   => 1,
			'postId' => -1
		], $criteria->getSortConditions());
		
		$criteria->sort('time DESC, title');
		$this->assertEquals([
			'time'   => -1,
			'postId' => -1,
			'title'  => 1
		], $criteria->getSortConditions());
	}
	
	public function testAggregationColumns() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->columns([
			'countAvg' => 'AVG(rate)',
			'countMax' => 'MAX(rate)'
		]);
		
		$this->assertEquals([
			['$group' => [
				'countAvg' => ['$avg' => '$rate'],
				'countMax' => ['$max' => '$rate'],
				'_id'      => null
			]]
		], $criteria->assemblePipeline());
	}
	
	public function testAggregationGroupBy() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->columns(['count' => 'SUM(rate)']);
		$criteria->groupBy('userId');
		
		$this->assertEquals([
			['$group' => [
				'count' => ['$sum' => '$rate'],
				'_id'   => '$userId'
			]]
		], $criteria->assemblePipeline());
	}
	
	public function testAggregationSort() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->columns(['count' => 'SUM(rate)']);
		$criteria->sort('userId ASC, time DESC');
		
		$this->assertEquals([
			['$group' => [
				'count' => ['$sum' => '$rate'],
				'_id'   => null
			]],
			['$sort' => [
				'userId' => 1,
				'time'   => -1
			]]
		], $criteria->assemblePipeline());
	}
	
	public function testAggregationLimit() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->columns(['count' => 'SUM(rate)']);
		$criteria->limit(10);
		
		$this->assertEquals([
			['$group' => [
				'count' => ['$sum' => '$rate'],
				'_id'   => null
			]],
			['$limit' => 10]
		], $criteria->assemblePipeline());
	}
	
	public function testAggregationOffset() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->columns(['count' => 'SUM(rate)']);
		$criteria->offset(10);
		
		$this->assertEquals([
			['$group' => [
				'count' => ['$sum' => '$rate'],
				'_id'   => null
			]],
			['$skip' => 10]
		], $criteria->assemblePipeline());
	}
	
	public function testAggregationMatch() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->columns(['count' => 'SUM(rate)']);
		
		$criteria->where('userId = ?', 10);
		$criteria->where('postId >= ?', 20);
		
		$this->assertEquals([
			['$match' => [
				'userId' => 10,
				'postId' => ['$gte' => 20]
			]],
			['$group' => [
				'count' => ['$sum' => '$rate'],
				'_id'   => null
			]]
		], $criteria->assemblePipeline());
	}
	
	public function testAggregationComplex() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->columns([
			'countAvg' => 'AVG(rate)',
			'countMax' => 'MAX(rate)'
		]);
		
		$criteria->where('userId = ?', 10);
		$criteria->where('postId >= ?', 20);
		
		$criteria->groupBy('userId');
		$criteria->sort('userId ASC, time DESC');
		$criteria->limit(10, 20);
		
		$this->assertEquals([
			['$match' => [
				'userId' => 10,
				'postId' => ['$gte' => 20]
			]],
			['$group' => [
				'countAvg' => ['$avg' => '$rate'],
				'countMax' => ['$max' => '$rate'],
				'_id'      => '$userId'
			]],
			['$sort' => [
				'userId' => 1,
				'time'   => -1
			]],
			['$skip'  => 20],
			['$limit' => 10]
		], $criteria->assemblePipeline());
	}
	
}