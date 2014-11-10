<?php

class DefinitionAbstractTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Test defintion
	 *
	 * @var ZExt\Di\Definition\DefinitionAbstract 
	 */
	protected $definition;
	
	public function setUp() {
		$this->definition = $this->getMockForAbstractClass('ZExt\Di\Definition\DefinitionAbstract');
		
		$this->definition
			->expects($this->any())
			->method('initService')
			->will($this->returnArgument(0));
	}
	
	public function testArguments() {
		$this->assertNull($this->definition->getArguments());
		
		$args = [
			'arg1' => 'value1',
			'srg2' => 'value2'
		];
		
		$this->definition->setArguments($args);
		
		$this->assertSame($args, $this->definition->getArguments());
	}
	
	public function testFactoryMode() {
		$this->assertFalse($this->definition->isFactory());
		
		$this->definition->setFactoryMode();
		
		$this->assertTrue($this->definition->isFactory());
	}
	
	public function testGetService() {
		$definition = $this->getMockForAbstractClass('ZExt\Di\Definition\DefinitionAbstract');
		
		$definition->expects($this->any())
		           ->method('initService')
				   ->will($this->returnCallback(function() {
					   return new stdClass();
				   }));
		
		$service = $definition->getService();
		
		$this->assertEquals(new stdClass(), $service);
		
		$service->name = 'qwerty';
		$this->assertEquals((object) ['name' => 'qwerty'], $definition->getService());
	}
	
	public function testGetServiceWithDefaultArgs() {
		$this->definition->setArguments('qwerty');
		
		$this->assertSame(['qwerty'], $this->definition->getService());
	}
	
	public function testGetServiceWithScalarArg() {
		$this->assertSame([12], $this->definition->getService(12));
	}
	
	public function testGetServiceWithArrayArgs() {
		$this->assertSame([12, true], $this->definition->getService([12, true]));
	}
	
	public function testGetServiceWithIteratorArgs() {
		$this->assertSame([12, true], $this->definition->getService(new ArrayIterator([12, true])));
	}
	
	public function testGetServiceWithConfigArgs() {
		$config = $this->getMock('ZExt\Config\ConfigInterface');
		
		$config->expects($this->any())
		       ->method('toArray')
		       ->will($this->returnValue([12, 34, 56]));
		
		$this->assertSame([12, 34, 56], $this->definition->getService($config));
	}
	
	public function testGetServiceWithArgDefiniton() {
		$argument = $this->getMock('ZExt\Di\Definition\Argument\ArgumentInterface');
		
		$argument->expects($this->any())
		         ->method('getValue')
		         ->will($this->returnValue(12));
		
		$this->assertSame([12], $this->definition->getService($argument));
	}
	
	public function testGetServiceByFactory() {
		$definition = $this->getMockForAbstractClass('ZExt\Di\Definition\DefinitionAbstract');
		$definition->setFactoryMode();
		
		$definition->expects($this->any())
		           ->method('initService')
				   ->will($this->returnCallback(function() {
					   return new stdClass();
				   }));
		
		$service = $definition->getService();
		
		$this->assertEquals(new stdClass(), $service);
		
		$service->id = 123;
		$this->assertEquals(new stdClass(), $definition->getService());
	}
	
	public function testGetServiceByFactoryWithArgs() {
		$this->definition->setFactoryMode();
		
		$this->assertNull($this->definition->getService(12));
		
		$this->definition->setArguments(12);
		
		$this->assertSame([12], $this->definition->getService());
	}
	
	public function testReset() {
		$definition = $this->getMockForAbstractClass('ZExt\Di\Definition\DefinitionAbstract');
		
		$definition->expects($this->any())
		           ->method('initService')
				   ->will($this->returnCallback(function() {
					   return new stdClass();
				   }));
				   
		$service = $definition->getService();
		$service->id = 12;
		
		$definition->reset();
		$this->assertEquals(new stdClass(), $definition->getService());
	}
	
	public function testResetWithArgs() {
		$definition = $this->getMockForAbstractClass('ZExt\Di\Definition\DefinitionAbstract');
		
		$definition->expects($this->any())
		           ->method('initService')
				   ->will($this->returnCallback(function() {
					   return new stdClass();
				   }));
				   
		$service = $definition->getService(100);
		$service->id = 12;
		
		$definition->reset(100);
		$this->assertEquals(new stdClass(), $definition->getService(100));
	}
	
	public function testResetOnSetArguments() {
		$definition = $this->getMockForAbstractClass('ZExt\Di\Definition\DefinitionAbstract');
		
		$definition->expects($this->any())
		           ->method('initService')
				   ->will($this->returnCallback(function() {
					   return new stdClass();
				   }));
				   
		$service = $definition->getService();
		$service->id = 12;
		
		$definition->setArguments([true]);
		$this->assertEquals(new stdClass(), $definition->getService());
	}
	
	public function testHasInitialized() {
		$definition = $this->getMockForAbstractClass('ZExt\Di\Definition\DefinitionAbstract');
		
		$definition->expects($this->any())
		           ->method('initService')
				   ->will($this->returnValue(new stdClass()));
		
		$this->assertFalse($definition->hasInitialized());
		
		$definition->getService();
		
		$this->assertTrue($definition->hasInitialized());
	}
	
	public function testHasInitializedWithArgs() {
		$definition = $this->getMockForAbstractClass('ZExt\Di\Definition\DefinitionAbstract');
		
		$definition->expects($this->any())
		           ->method('initService')
				   ->will($this->returnValue(new stdClass()));
		
		$definition->getService();
		$this->assertFalse($definition->hasInitialized('qwerty'));
		
		$definition->getService('qwerty');
		$this->assertTrue($definition->hasInitialized('qwerty'));
		$this->assertFalse($definition->hasInitialized('asdfgh'));
	}
	
	public function testSerialization() {
		$this->definition->getService();
		$this->definition->getService(12);
		
		$definition = unserialize(serialize($this->definition));
		
		$this->assertFalse($definition->hasInitialized());
		$this->assertFalse($definition->hasInitialized(12));
		
		$definition->expects($this->any())
		           ->method('initService')
				   ->will($this->returnValue(new stdClass()));
		
		$this->assertEquals(new stdClass(), $definition->getService());
	}
	
}