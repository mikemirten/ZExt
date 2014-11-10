<?php

use ZExt\Di\Definition\ClassDefinition;

class ClassDefinitionTest extends PHPUnit_Framework_TestCase {
	
	public function testGetService() {
		$definition = new ClassDefinition('stdClass');
		
		$service = $definition->getService();
		
		$this->assertEquals(new stdClass(), $service);
		
		$service->id = 100;
		$this->assertEquals((object) ['id' => 100], $definition->getService());
	}
	
	public function testGetServiceByFactory() {
		$definition = new ClassDefinition('stdClass');
		$definition->setFactoryMode();
		
		$service = $definition->getService();
		$service->id = 100;
		
		$this->assertEquals(new stdClass(), $definition->getService());
	}
	
	public function testGetServiceWithArgs() {
		$definition = new ClassDefinition('ArrayIterator');
		
		$service = $definition->getService([[1, 2, 3]]);
		
		$this->assertSame([1, 2, 3], iterator_to_array($service));
	}
	
	public function testSerialization() {
		$definition = new ClassDefinition('ArrayIterator', [[1, 2, 3]]);
		
		$service = $definition->getService();
		
		$this->assertSame([1, 2, 3], iterator_to_array($service));
	}
	
}