<?php

use ZExt\Di\Definition\PrototypeDefinition;

class PrototypeDefinitionTest extends PHPUnit_Framework_TestCase {

	/**
	 * Test definition
	 *
	 * @var ZExt\Di\Definition\PrototypeDefinition
	 */
	protected $definition;
	
	public function setUp() {
		$prototype = new stdClass();
		$prototype->id = 100;
		
		$this->definition = new PrototypeDefinition($prototype);
	}
	
	public function testGetService() {
		$service = $this->definition->getService();
		
		$this->assertEquals((object) ['id' => 100], $service);
		
		$service->id = 200;
		$this->assertEquals((object) ['id' => 100], $this->definition->getService());
	}
	
	/**
	 * @expectedException RuntimeException
	 */
	public function testSetFactoryMode() {
		$this->definition->setFactoryMode(false);
	}
	
	public function testIsFactory() {
		$this->assertTrue($this->definition->isFactory());
	}
	
	public function testSerialization() {
		$definition = unserialize(serialize($this->definition));
		
		$this->assertEquals((object) ['id' => 100], $this->definition->getService());
	}
	
}