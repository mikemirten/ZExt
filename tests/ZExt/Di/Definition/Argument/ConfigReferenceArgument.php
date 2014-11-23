<?php

use ZExt\Di\Definition\Argument\ConfigReferenceArgument;

class ConfigReferenceArgumentTest extends PHPUnit_Framework_TestCase {
	
	public function testGetValue() {
		$config = $this->getMock('ZExt\Config\ConfigInterface');
		
		$config->expects($this->any())
		       ->method('get')
		       ->with($this->equalTo('param1'))
		       ->will($this->returnValue('qwerty'));
		
		$argument = new ConfigReferenceArgument($config, 'param1');
		
		$this->assertSame('qwerty', $argument->getValue());
	}
	
}