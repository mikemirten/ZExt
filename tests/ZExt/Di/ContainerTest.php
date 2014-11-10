<?php

use ZExt\Di\Container;

class ContainerTest extends PHPUnit_Framework_TestCase {
	
	public function testImplements() {
		$container = new Container();
		
		$this->assertInstanceOf('ZExt\Di\ContainerInterface', $container);
		$this->assertInstanceOf('ZExt\Di\LocatorInterface', $container);
		$this->assertInstanceOf('ZExt\Di\DefinitionAwareInterface', $container);
	}
	
	public function testServices() {
		$container = new Container();
		
		$this->assertFalse($container->has('service'));
		
		$definition = $this->getMock('ZExt\Di\Definition\DefinitionInterface');
		
		$definition->expects($this->any())
		           ->method('getService')
		           ->will($this->returnValue((object) ['id' => 100]));
		
		$definition->expects($this->any())
		           ->method('hasInitialized')
		           ->will($this->returnValue(false));
		
		$container->set('service', $definition);
		
		$this->assertTrue($container->has('service'));
		$this->assertFalse($container->hasInitialized('service'));
		$this->assertEquals((object) ['id' => 100], $container->get('service'));
	}
	
	public function testServicesWithArgs() {
		$container = new Container();
		
		$this->assertFalse($container->has('service'));
		
		$definition = $this->getMock('ZExt\Di\Definition\DefinitionInterface');
		
		$definition->expects($this->any())
		           ->method('getService')
		           ->with($this->equalTo(100))
		           ->will($this->returnValue((object) ['id' => 100]));
		
		$definition->expects($this->any())
		           ->method('hasInitialized')
		           ->with($this->equalTo(100))
		           ->will($this->returnValue(false));
		
		$container->set('service', $definition);
		
		$this->assertTrue($container->has('service'));
		$this->assertFalse($container->hasInitialized('service', 100));
		$this->assertEquals((object) ['id' => 100], $container->get('service', 100));
	}
	
	/**
	 * @expectedException ZExt\Di\Exceptions\ServiceOverride
	 */
	public function testExistedService() {
		$container = new Container();
		$container->set('service', 1);
		$container->set('service', 2);
	}
	
	public function testSetAlias() {
		$container = new Container();
		$container->set('service', 1);
		$container->setAlias('service', 'alias');
		
		$this->assertTrue($container->has('alias'));
	}
	
	/**
	 * @expectedException ZExt\Di\Exceptions\ServiceOverride
	 */
	public function testSetAliasExistedService() {
		$container = new Container();
		$container->set('service1', 1);
		$container->set('service2', 2);
		$container->setAlias('service1', 'service2');
	}
	
	/**
	 * @expectedException ZExt\Di\Exceptions\ServiceNotFound
	 */
	public function testServiceNotFound() {
		$container = new Container();
		
		$container->get('service');
	}
	
	/**
	 * @expectedException ZExt\Di\Exceptions\ServiceNotFound
	 */
	public function testServiceNotFound2() {
		$container = new Container();
		
		$container->hasInitialized('service');
	}
	
	/**
	 * @expectedException ZExt\Di\Exceptions\ServiceNotFound
	 */
	public function testDefinitionNotFound() {
		$container = new Container();
		
		$container->getDefinition('service');
	}
	
	public function testDefinitionsNormalization() {
		$container = new Container();
		
		$container->set('callback', function() {});
		$this->assertInstanceOf(
			'ZExt\Di\Definition\CallbackDefinition',
			$container->getDefinition('callback')
		);
		
		$container->set('class', 'stdClass');
		$this->assertInstanceOf(
			'ZExt\Di\Definition\ClassDefinition',
			$container->getDefinition('class')
		);
		
		$container->set('instance', new stdClass());
		$this->assertInstanceOf(
			'ZExt\Di\Definition\InstanceDefinition',
			$container->getDefinition('instance')
		);
	}
	
	public function testRemove() {
		$container = new Container();
		$container->set('service', 1);
		
		$container->remove('service');
		$this->assertFalse($container->has('service'));
	}
	
	public function testChainedLocator() {
		$locator = $this->getMock('ZExt\Di\LocatorInterface');
		
		$locator->expects($this->any())
		        ->method('has')
				->with($this->equalTo('service'))
				->will($this->returnValue(true));
		
		$locator->expects($this->any())
		        ->method('get')
				->with($this->equalTo('service'))
				->will($this->returnValue(new stdClass()));
		
		$container = new Container();
		$container->addLocator($locator);
		
		$this->assertTrue($container->has('service'));
		$this->assertEquals(new stdClass(), $container->get('service'));
	}
	
	/**
	 * @expectedException ZExt\Di\Exceptions\ServiceNotFound
	 */
	public function testUnableToProvideDefinition() {
		$locator = $this->getMock('ZExt\Di\LocatorInterface');
		
		$locator->expects($this->any())
		        ->method('has')
				->with($this->equalTo('service'))
				->will($this->returnValue(true));
		
		$container = new Container();
		$container->addLocator($locator);
		
		$this->assertFalse($container->hasInitialized('service'));
	}
	
	public function testChainedLocatorGetDefinition() {
		$definition = $this->getMock('ZExt\Di\Definition\DefinitionInterface');
		$locator    = $this->getMock('ZExt\Di\Container');
		
		$locator->expects($this->any())
		        ->method('has')
				->with($this->equalTo('service'))
				->will($this->returnValue(true));
		
		$locator->expects($this->any())
		        ->method('getDefinition')
				->with($this->equalTo('service'))
				->will($this->returnValue($definition));
		
		$container = new Container();
		$container->addLocator($locator);
		
		$this->assertInstanceOf('ZExt\Di\Definition\DefinitionInterface', $container->getDefinition('service'));
	}
	
}
