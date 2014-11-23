<?php

use ZExt\Di\Configurator;
use ZExt\Di\Container;

class ConfiguratorTest extends PHPUnit_Framework_TestCase {
	
	public function testSetService() {
		$reader = $this->getMock('ZExt\Di\Config\ReaderInterface');
		
		$reader->expects($this->any())
		       ->method('getIncludes')
		       ->will($this->returnValue([]));
		
		$reader->expects($this->any())
		       ->method('getParameters')
		       ->will($this->returnValue(new stdClass()));
		
		$reader->expects($this->any())
		       ->method('getServices')
		       ->will($this->returnValue((object) [
				   'service' => (object) [
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
				   ]
			   ]));
		
		$reader->expects($this->any())
		       ->method('getInitializers')
		       ->will($this->returnValue(new stdClass()));
		
		$container = new Container();
		
		$configurator = new Configurator($container);
		$configurator->addConfigReader($reader)->configure();
		
		$descriptor = $container->getDefinition('service');
		
		$this->assertInstanceOf('ZExt\Di\Definition\ClassDefinition', $descriptor);
		$this->assertSame('TestClass', $descriptor->getClass());
		$this->assertTrue($descriptor->isFactory());
		
		$args = $descriptor->getArguments();
		
		$this->assertSame(12, $args[0]);
		$this->assertSame(34, $args[1][0]);
		$this->assertInstanceOf('ZExt\Di\Definition\Argument\ServiceReferenceArgument', $args[1][1]);
		
		$serviceArgs = $args[1][1]->getArguments();
		
		$this->assertSame('localhost', $serviceArgs[0]);
		$this->assertInstanceOf('ZExt\Di\Definition\Argument\ServiceReferenceArgument', $serviceArgs[1]);
	}
	
	public function testSetServiceWithParameters() {
		$reader = $this->getMock('ZExt\Di\Config\ReaderInterface');
		
		$reader->expects($this->any())
		       ->method('getIncludes')
		       ->will($this->returnValue([]));
		
		$reader->expects($this->any())
		       ->method('getParameters')
		       ->will($this->returnValue((object) [
				   'param1' => (object) [
					   'type'  => 'value',
					   'value' => 123456
				   ],
				   'param2' => (object) [
					   'type'  => 'value',
					   'value' => 'qwerty'
				   ]
			   ]));
		
		$reader->expects($this->any())
		       ->method('getServices')
		       ->will($this->returnValue((object) [
				   'service' => (object) [
						'type'      => 'class',
						'class'     => 'TestClass',
						'arguments' => [
							(object) [
								'type' => 'parameter',
								'name' => 'param1'
							],
							(object) [
								'type'     => 'parameter',
								'name'     => 'param2',
								'deferred' => true
							]
						]
				   ]
			   ]));
		
		$reader->expects($this->any())
		       ->method('getInitializers')
		       ->will($this->returnValue(new stdClass()));
		
		$container = new Container();
		
		$configurator = new Configurator($container);
		$configurator->addConfigReader($reader)->configure();
		
		$args = $container->getDefinition('service')->getArguments();
		
		$this->assertSame(123456, $args[0]);
		$this->assertInstanceOf('ZExt\Di\Definition\Argument\ConfigReferenceArgument', $args[1]);
	}
	
}