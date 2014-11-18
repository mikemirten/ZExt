<?php

use ZExt\Di\Configurator;
use ZExt\Di\Container;

class ConfiguratorTest extends PHPUnit_Framework_TestCase {
	
	public function testSetService() {
		$config = $this->getMock('ZExt\Di\Config\ReaderInterface');
		
		$config->expects($this->any())
		       ->method('getConfiguration')
		       ->will($this->returnValue((object) [
				   'services' => [ 'service' => (object) [
						'type'      => 'class',
						'class'     => 'TestClass',
						'factory'   => true,
						'arguments' => [
							(object) ['type' => 'value', 'value' => 12],
							(object) ['type' => 'value', 'value' => [
								(object) ['type' => 'value', 'value' => 34],
								(object) [
									'type'      => 'service',
									'id'        => 'cache',
									'arguments' => [
										(object) ['type' => 'value', 'value' => 'localhost'],
										(object) ['type' => 'service', 'id' => 'memcache'],
									]
								]
							]]
						]
				   ]]
			   ]));
		
		$container = new Container();
		
		$configurator = new Configurator($container);
		$configurator->addConfig($config)->configure();
		
		$descriptor = $container->getDefinition('service');
		
		$this->assertInstanceOf('ZExt\Di\Definition\ClassDefinition', $descriptor);
		$this->assertSame('TestClass', $descriptor->getClass());
		$this->assertTrue($descriptor->isFactory());
		
		$args = $descriptor->getArguments();
		
		$this->assertSame(12, $args[0]);
		$this->assertSame(34, $args[1][0]);
		$this->assertInstanceOf('ZExt\Di\Definition\Argument\ArgumentInterface', $args[1][1]);
		
		$serviceArgs = $args[1][1]->getArguments();
		
		$this->assertSame('localhost', $serviceArgs[0]);
		$this->assertInstanceOf('ZExt\Di\Definition\Argument\ArgumentInterface', $serviceArgs[1]);
	}
	
	
}