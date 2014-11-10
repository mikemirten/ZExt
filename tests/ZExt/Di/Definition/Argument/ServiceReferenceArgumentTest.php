<?php

use ZExt\Di\Definition\Argument\ServiceReferenceArgument;

class ServiceReferenceArgumentTest extends PHPUnit_Framework_TestCase {
	
	public function testGetValue() {
		$locator = $this->getMock('ZExt\Di\LocatorInterface');
		
		$locator->expects($this->any())
		        ->method('get')
				->with($this->equalTo('service'))
		        ->will($this->returnValue((object) ['id' => 100]));
		
		$argument = new ServiceReferenceArgument($locator, 'service');
		
		$this->assertEquals((object) ['id' => 100], $argument->getValue());
	}
	
}
