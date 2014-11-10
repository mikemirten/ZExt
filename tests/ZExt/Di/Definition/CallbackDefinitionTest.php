<?php

use ZExt\Di\Definition\CallbackDefinition;

class CallbackDefinitionTest extends PHPUnit_Framework_TestCase {
	
	public function testGetService() {
		$definition = new CallbackDefinition(function() {
			return (object) ['id' => 100];
		});
		
		$this->assertEquals((object) ['id' => 100], $definition->getService());
	}
	
	public function testGetServiceByFactory() {
		$definition = new CallbackDefinition(function() {
			return (object) ['id' => 100];
		});
		$definition->setFactoryMode();
		
		$service = $definition->getService();
		$service->id = 200;
		
		$this->assertEquals((object) ['id' => 100], $definition->getService());
	}
	
	public function testGetServiceWithArgs() {
		$definition = new CallbackDefinition(function($id) {
			return (object) ['id' => $id];
		});
		
		$this->assertEquals((object) ['id' => 100], $definition->getService(100));
	}
	
	public function testSerialization() {
		$definition = new CallbackDefinition(function($id) {
			return (object) ['id' => $id];
		}, 100);
		
		$unserialized = unserialize(serialize($definition));
		
		$this->assertEquals((object) ['id' => 100], $unserialized->getService());
	}
	
}