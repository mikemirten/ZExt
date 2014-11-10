<?php

use ZExt\Di\Definition\InstanceDefinition;

class InstanceDefinitionTest extends PHPUnit_Framework_TestCase {
	
	
	/**
	 * Test definition
	 *
	 * @var ZExt\Di\Definition\InstanceDefinition
	 */
	protected $definition;
	
	public function setUp() {
		$this->definition = new InstanceDefinition(new stdClass());
	}
	
	public function testGetService() {
		$service = $this->definition->getService();
		
		$this->assertEquals(new stdClass(), $service);
		
		$service->id = 100;
		$this->assertEquals((object) ['id' => 100], $this->definition->getService());
	}
	
	/**
	 * @expectedException RuntimeException
	 */
	public function testSetFactoryMode() {
		$this->definition->setFactoryMode(false);
	}
	
	/**
	 * @expectedException RuntimeException
	 */
	public function testReset() {
		$this->definition->reset();
	}
	
	public function testIsFactory() {
		$this->assertFalse($this->definition->isFactory());
	}
	
	public function testSerialization() {
		$definition = new InstanceDefinition([1, 2, 3]);
		
		$this->assertSame([1, 2, 3], $definition->getService());
	}
	
}