<?php

use ZExt\Cache\Backend\Decorators\Taggable;

require_once __DIR__ . '/../BackendTestCase.php';

class TaggableTest extends PHPUnit_Framework_TestCase {
	
	public function testSet() {
		$backend   = new BackendTestCase();
		$decorator = new Taggable($backend);
		
		$decorator->set('test1', 10);
		$decorator->set('test2', 20);
		
		$this->assertSame(10, $backend->get('test1'));
		$this->assertSame(20, $backend->get('test2'));
	}
	
	public function testSetWithTag() {
		$backendData = new BackendTestCase();
		$backendTags = new BackendTestCase();
		$decorator   = new Taggable($backendData, $backendTags);
		
		$decorator->set('test1', 10, 0, 'tag1');
		
		$this->assertSame(10, $backendData->get('test1'));
		$this->assertContains('test1', $backendTags->get('TAG_tag1'));
		
		$decorator->set('test2', 20, 0, 'tag1');
		
		$this->assertSame(20, $backendData->get('test2'));
		$this->assertContains('test1', $backendTags->get('TAG_tag1'));
		$this->assertContains('test2', $backendTags->get('TAG_tag1'));
	}
	
	public function testSetWithManyTags() {
		$backendData = new BackendTestCase();
		$backendTags = new BackendTestCase();
		$decorator   = new Taggable($backendData, $backendTags);
		
		$decorator->set('test1', 10, 0, ['tag1', 'tag2']);
		
		$this->assertSame(10, $backendData->get('test1'));
		$this->assertContains('test1', $backendTags->get('TAG_tag1'));
		$this->assertContains('test1', $backendTags->get('TAG_tag2'));
		
		$decorator->set('test2', 20, 0, ['tag1', 'tag2']);
		
		$this->assertSame(20, $backendData->get('test2'));
		$this->assertContains('test1', $backendTags->get('TAG_tag1'));
		$this->assertContains('test2', $backendTags->get('TAG_tag2'));
		$this->assertContains('test1', $backendTags->get('TAG_tag1'));
		$this->assertContains('test2', $backendTags->get('TAG_tag2'));
	}
	
	public function testSetMany() {
		$backend   = new BackendTestCase();
		$decorator = new Taggable($backend);
		
		$decorator->setMany([
			'test1' => 10,
			'test2' => 20
		]);
		
		$this->assertSame(10, $backend->get('test1'));
		$this->assertSame(20, $backend->get('test2'));
	}
	
	public function testSetManyWithTag() {
		$backendData = new BackendTestCase();
		$backendTags = new BackendTestCase();
		$decorator   = new Taggable($backendData, $backendTags);
		
		$decorator->setMany([
			'test1' => 10,
			'test2' => 20
		], 0, 'tag1');
		
		$this->assertSame(10, $backendData->get('test1'));
		$this->assertSame(20, $backendData->get('test2'));
		$this->assertContains('test1', $backendTags->get('TAG_tag1'));
		$this->assertContains('test2', $backendTags->get('TAG_tag1'));
		
		$decorator->setMany([
			'test3' => 30,
			'test4' => 40
		], 0, 'tag1');
		
		$this->assertSame(30, $backendData->get('test3'));
		$this->assertSame(40, $backendData->get('test4'));
		$this->assertContains('test1', $backendTags->get('TAG_tag1'));
		$this->assertContains('test2', $backendTags->get('TAG_tag1'));
		$this->assertContains('test3', $backendTags->get('TAG_tag1'));
		$this->assertContains('test4', $backendTags->get('TAG_tag1'));
	}
	
	public function testSetManyWithManyTags() {
		$backendData = new BackendTestCase();
		$backendTags = new BackendTestCase();
		$decorator   = new Taggable($backendData, $backendTags);
		
		$decorator->setMany([
			'test1' => 10,
			'test2' => 20
		], 0, ['tag1', 'tag2']);
		
		$this->assertSame(10, $backendData->get('test1'));
		$this->assertSame(20, $backendData->get('test2'));
		$this->assertContains('test1', $backendTags->get('TAG_tag1'));
		$this->assertContains('test2', $backendTags->get('TAG_tag1'));
		$this->assertContains('test1', $backendTags->get('TAG_tag2'));
		$this->assertContains('test2', $backendTags->get('TAG_tag2'));
		
		$decorator->setMany([
			'test3' => 30,
			'test4' => 40
		], 0, ['tag1', 'tag2']);
		
		$this->assertSame(30, $backendData->get('test3'));
		$this->assertSame(40, $backendData->get('test4'));
		$this->assertContains('test1', $backendTags->get('TAG_tag1'));
		$this->assertContains('test2', $backendTags->get('TAG_tag1'));
		$this->assertContains('test3', $backendTags->get('TAG_tag1'));
		$this->assertContains('test4', $backendTags->get('TAG_tag1'));
		$this->assertContains('test1', $backendTags->get('TAG_tag2'));
		$this->assertContains('test2', $backendTags->get('TAG_tag2'));
		$this->assertContains('test3', $backendTags->get('TAG_tag2'));
		$this->assertContains('test4', $backendTags->get('TAG_tag2'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testGet($data) {
		$backend   = new BackendTestCase($data);
		$decorator = new Taggable($backend);
		
		$this->assertSame(10, $decorator->get('test1'));
		$this->assertSame(20, $decorator->get('test2'));
		
		$this->assertNull($decorator->get('test0'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testGetMany($data) {
		$backend   = new BackendTestCase($data);
		$decorator = new Taggable($backend);
		
		$this->assertSame($data, $decorator->getMany(['test1', 'test2', 'test3', 'testN']));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testGetByTag($data, $tags) {
		$backendData = new BackendTestCase($data);
		$backendTags = new BackendTestCase($tags);
		$decorator   = new Taggable($backendData, $backendTags);
		
		$this->assertSame([
			'test1' => 10,
			'test2' => 20
		], $decorator->getByTag('tag1'));
		
		$this->assertSame([
			'test2' => 20,
			'test3' => 30
		], $decorator->getByTag('tag2'));
		
		$this->assertEmpty($decorator->getByTag('tagE'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testGetByManyTags($data, $tags) {
		$backendData = new BackendTestCase($data);
		$backendTags = new BackendTestCase($tags);
		$decorator   = new Taggable($backendData, $backendTags);
		
		$this->assertSame([
			'test1' => 10,
			'test2' => 20,
			'test3' => 30
		], $decorator->getByTag(['tag1', 'tag2']));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testGetByManyTagsIntersection($data, $tags) {
		$backendData = new BackendTestCase($data);
		$backendTags = new BackendTestCase($tags);
		$decorator   = new Taggable($backendData, $backendTags);
		
		$this->assertSame(['test2' => 20], $decorator->getByTag(['tag1', 'tag2'], true));
	}
	
	public function testHas() {
		$backend   = new BackendTestCase(['test' => 10]);
		$decorator = new Taggable($backend);
		
		$this->assertTrue($decorator->has('test'));
		$this->assertFalse($decorator->has('test0'));
	}
	
	public function testRemove() {
		$backend   = new BackendTestCase(['test' => 10]);
		$decorator = new Taggable($backend);
		
		$decorator->remove('test');
		
		$this->assertFalse($backend->has('test'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testRemoveMany($data) {
		$backend   = new BackendTestCase($data);
		$decorator = new Taggable($backend);
		
		$decorator->removeMany(['test1', 'test2']);
		
		$this->assertFalse($backend->has('test1'));
		$this->assertFalse($backend->has('test2'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testRemoveByTag1($data, $tags) {
		$backendData = new BackendTestCase($data);
		$backendTags = new BackendTestCase($tags);
		$decorator   = new Taggable($backendData, $backendTags);
		
		$decorator->removeByTag('tag1');
		
		$this->assertSame(['test3' => 30], $backendData->getData());
		$this->assertFalse($backendTags->has('TAG_tag1'));
		$this->assertTrue($backendTags->has('TAG_tag2'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testRemoveByTag2($data, $tags) {
		$backendData = new BackendTestCase($data);
		$backendTags = new BackendTestCase($tags);
		$decorator   = new Taggable($backendData, $backendTags);
		
		$decorator->removeByTag('tag2');
		
		$this->assertSame(['test1' => 10], $backendData->getData());
		$this->assertTrue($backendTags->has('TAG_tag1'));
		$this->assertFalse($backendTags->has('TAG_tag2'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testRemoveByTag3($data, $tags) {
		$backendData = new BackendTestCase($data);
		$backendTags = new BackendTestCase($tags);
		$decorator   = new Taggable($backendData, $backendTags);
		
		$decorator->removeByTag('tagE');
		
		$this->assertSame($data, $backendData->getData());
		$this->assertTrue($backendTags->has('TAG_tag1'));
		$this->assertTrue($backendTags->has('TAG_tag2'));
		$this->assertFalse($backendTags->has('TAG_tagE'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testRemoveByManyTags($data, $tags) {
		$backendData = new BackendTestCase($data);
		$backendTags = new BackendTestCase($tags);
		$decorator   = new Taggable($backendData, $backendTags);
		
		$decorator->removeByTag(['tag1', 'tag2']);
		
		$this->assertSame([], $backendData->getData());
		$this->assertFalse($backendTags->has('TAG_tag1'));
		$this->assertFalse($backendTags->has('TAG_tag2'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testRemoveByManyTagsIntersection1($data, $tags) {
		$backendData = new BackendTestCase($data);
		$backendTags = new BackendTestCase($tags);
		$decorator   = new Taggable($backendData, $backendTags);
		
		$decorator->removeByTag(['tag1', 'tag2'], true);
		
		$this->assertSame([
			'test1' => 10,
			'test3' => 30
		], $backendData->getData());
		
		$this->assertTrue($backendTags->has('TAG_tag1'));
		$this->assertTrue($backendTags->has('TAG_tag2'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testRemoveByManyTagsIntersection2($data, $tags) {
		$backendData = new BackendTestCase($data);
		$backendTags = new BackendTestCase($tags);
		$decorator   = new Taggable($backendData, $backendTags);
		
		$decorator->removeByTag(['tag1', 'tag2', 'tagE'], true);
		
		$this->assertSame($data, $backendData->getData());
		
		$this->assertTrue($backendTags->has('TAG_tag1'));
		$this->assertTrue($backendTags->has('TAG_tag2'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testIncDec($data) {
		$backend   = new BackendTestCase($data);
		$decorator = new Taggable($backend);
		
		$decorator->inc('test1');
		$decorator->inc('test2', 10);
		
		$this->assertSame(11, $backend->get('test1'));
		$this->assertSame(30, $backend->get('test2'));
		
		$decorator->dec('test1');
		$decorator->dec('test2', 5);
		
		$this->assertSame(10, $backend->get('test1'));
		$this->assertSame(25, $backend->get('test2'));
	}
	
	/**
	 * @dataProvider cacheSourceProvider
	 */
	public function testFlush($data, $tags) {
		$backendData = new BackendTestCase($data);
		$backendTags = new BackendTestCase($tags);
		$decorator   = new Taggable($backendData, $backendTags);
		
		$decorator->flush();
		
		$this->assertEmpty($backendData->getData());
		$this->assertEmpty($backendTags->getData());
	}
	
	public function cacheSourceProvider() {
		return [
			[
				[
					'test1' => 10,
					'test2' => 20,
					'test3' => 30,
				],
				[
					'TAG_tag1' => ['test1', 'test2'],
					'TAG_tag2' => ['test2', 'test3'],
					'TAG_tagE' => ['testA', 'testB']
				]
			]
		];
	}
	
}