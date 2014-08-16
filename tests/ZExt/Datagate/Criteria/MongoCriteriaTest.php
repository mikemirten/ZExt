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
		
		$this->assertEquals([
			'postId' => 200,
			'active' => true,
			'role'   => 'admin'
		], $criteria->assemble());
	}
	
	public function testWhereNotEqual() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId != ?', 200);
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_NOT_EQUAL => 200]
		], $criteria->assemble());
		
		$criteria->where('active != ?', true);
		$criteria->where('role != admin');
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_NOT_EQUAL => 200],
			'active' => [Criteria::MONGO_NOT_EQUAL => true],
			'role'   => [Criteria::MONGO_NOT_EQUAL => 'admin'],
		], $criteria->assemble());
	}
	
	public function testWhereIn() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId in(?)', [1, 2, 3, 4]);
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_IN => [1, 2, 3, 4]]
		], $criteria->assemble());
		
		$criteria->where('role in(?)', ['guest', 'admin']);
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_IN => [1, 2, 3, 4]],
			'role'   => [Criteria::MONGO_IN => ['guest', 'admin']]
		], $criteria->assemble());
	}
	
	public function testWhereNotIn() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId not in(?)', [1, 2, 3, 4]);
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_NOT_IN => [1, 2, 3, 4]]
		], $criteria->assemble());
		
		$criteria->where('role not in(?)', ['guest', 'admin']);
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_NOT_IN => [1, 2, 3, 4]],
			'role'   => [Criteria::MONGO_NOT_IN => ['guest', 'admin']]
		], $criteria->assemble());
	}
	
	public function testWhereLessThan() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId < ?', 100);
		$criteria->where('rate < 10');
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_LESS => 100],
			'rate'   => [Criteria::MONGO_LESS => 10]
		], $criteria->assemble());
		
		$criteria->where('userId <= 200', 200);
		$criteria->where('status <= 100');
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_LESS => 100],
			'userId' => [Criteria::MONGO_LESS_EQUAL => 200],
			'rate'   => [Criteria::MONGO_LESS => 10],
			'status' => [Criteria::MONGO_LESS_EQUAL => 100],
		], $criteria->assemble());
	}
	
	public function testWhereMoreThan() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId > ?', 100);
		$criteria->where('rate > 10');
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_MORE => 100],
			'rate'   => [Criteria::MONGO_MORE => 10]
		], $criteria->assemble());
		
		$criteria->where('userId >= 200', 200);
		$criteria->where('status >= 100');
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_MORE => 100],
			'userId' => [Criteria::MONGO_MORE_EQUAL => 200],
			'rate'   => [Criteria::MONGO_MORE => 10],
			'status' => [Criteria::MONGO_MORE_EQUAL => 100],
		], $criteria->assemble());
	}
	
	public function testWhereInArray() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId in array(?)', [1, 2, 3, 4]);
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_IN_ARRAY => [1, 2, 3, 4]]
		], $criteria->assemble());
	}
	
	public function testWhereExists() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId exists(?)', true);
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_EXISTS => true]
		], $criteria->assemble());
		
		$criteria->where('userId exists(?)', false);
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_EXISTS => true],
			'userId' => [Criteria::MONGO_EXISTS => false]
		], $criteria->assemble());
	}
	
	public function testWhereType() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('postId type(?)', Criteria::MONGO_TYPE_INT32);
		
		$this->assertEquals([
			'postId' => [Criteria::MONGO_TYPE => Criteria::MONGO_TYPE_INT32]
		], $criteria->assemble());
	}
	
	public function testWhereIsArray() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('roles is array()');
		
		$this->assertEquals([
			'roles'   => [Criteria::MONGO_TYPE => Criteria::MONGO_TYPE_ARRAY]
		], $criteria->assemble());
	}
	
	public function testWhereIsInt() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('role is int()');
		
		$this->assertEquals([
			'role'   => [Criteria::MONGO_TYPE => Criteria::MONGO_TYPE_INT32]
		], $criteria->assemble());
	}
	
	public function testWhereIsString() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('role is string()');
		
		$this->assertEquals([
			'role'   => [Criteria::MONGO_TYPE => Criteria::MONGO_TYPE_STRING]
		], $criteria->assemble());
	}
	
	public function testWhereIsBool() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('enabled is bool()');
		
		$this->assertEquals([
			'enabled'   => [Criteria::MONGO_TYPE => Criteria::MONGO_TYPE_BOOLEAN]
		], $criteria->assemble());
	}
	
	public function testWhereIsNull() {
		$datagate = new MongoTestDatagate();
		$criteria = $datagate->query();
		
		$criteria->where('enabled is null()');
		
		$this->assertEquals([
			'enabled'   => [Criteria::MONGO_TYPE => Criteria::MONGO_TYPE_NULL]
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
			'roles'   => [Criteria::MONGO_SIZE => 10],
			'friends' => [Criteria::MONGO_SIZE => 5]
		], $criteria->assemble());
		
		$criteria = $datagate->query();
		
		$criteria->where('roles array count(?)', 20);
		$criteria->where('friends array count(10)');
		
		$this->assertEquals([
			'roles'   => [Criteria::MONGO_SIZE => 20],
			'friends' => [Criteria::MONGO_SIZE => 10]
		], $criteria->assemble());
	}
	
}