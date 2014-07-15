<?php

use ZExt\Config\Reader\Ini;

class IniTest extends PHPUnit_Framework_TestCase {
	
	public function testNoSections() {
		$reader  = new Ini();
		$options = [
			Ini::OPTION_MODE => Ini::SECTIONS_IGNORE
		];
				
		$config = $reader->parse(file_get_contents(__DIR__ . '/Ini/simple.ini'), $options);
		
		$this->assertEquals('localhost', $config['host']);
		$this->assertSame(8080, $config['port']);
		$this->assertSame([1, 2], $config['value']);
		$this->assertSame(0.11, $config['float_value1']);
		$this->assertSame(0.011, $config['float_value2']);
		
		$this->assertEquals('127.0.0.1', $config['db']['host']);
		$this->assertSame(1010, $config['db']['port']);
		$this->assertSame([3, 4], $config['srv']['value']);
	}
	
	public function testSectionsIgnore() {
		$reader = new Ini();
		$options = [
			Ini::OPTION_MODE => Ini::SECTIONS_IGNORE
		];
		
		$config = $reader->parse(file_get_contents(__DIR__ . '/Ini/sections.ini'), $options);
		
		$this->assertEquals('localhost', $config['host']);
		$this->assertSame(8080, $config['port']);
		$this->assertSame([1, 2, 3, 4, 5, 6], $config['value']);
		$this->assertSame(0.11, $config['float_value1']);
		$this->assertSame(0.011, $config['float_value2']);
		
		$this->assertEquals('127.0.0.2', $config['db']['host']);
		$this->assertSame(2020, $config['db']['port']);
		$this->assertSame([3, 4, 5, 6], $config['srv']['value']);
		$this->assertSame([1, 2, 'abc', 'zxc'], $config['owr']['value']);
	}
	
	public function testOneSection() {
		$reader  = new Ini();
		$options = [
			Ini::OPTION_MODE    => Ini::SECTIONS_PICK,
			Ini::OPTION_SECTION => 'app'
		];
		
		$config = $reader->parse(file_get_contents(__DIR__ . '/Ini/sections.ini'), $options);
		
		$this->assertEquals('localhost', $config['host']);
		$this->assertSame(8080, $config['port']);
		$this->assertSame([1, 2], $config['value']);
		
		$this->assertEquals('127.0.0.1', $config['db']['host']);
		$this->assertSame(1010, $config['db']['port']);
		$this->assertSame([3, 4], $config['srv']['value']);
		$this->assertSame([1, 2], $config['owr']['value']);
		
		$this->assertArrayNotHasKey('test', $config);
		$this->assertArrayNotHasKey('path', $config);
		$this->assertArrayNotHasKey('float_value1', $config);
	}
	
	public function testManySections() {
		$reader  = new Ini();
		$options = [
			Ini::OPTION_MODE    => Ini::SECTIONS_PICK,
			Ini::OPTION_SECTION => ['app', 'test', 'test2']
		];
		
		$config = $reader->parse(file_get_contents(__DIR__ . '/Ini/sections.ini'), $options);
		
		$this->assertEquals('localhost', $config['host']);
		$this->assertSame(8080, $config['port']);
		$this->assertSame([5, 6], $config['value']);
		
		$this->assertEquals('127.0.0.2', $config['db']['host']);
		$this->assertSame(2020, $config['db']['port']);
		$this->assertSame([5, 6], $config['srv']['value']);
		$this->assertSame(['abc', 'zxc'], $config['owr']['value']);
		
		$this->assertArrayNotHasKey('float', $config);
		$this->assertArrayNotHasKey('float_value1', $config);
	}
	
	public function testSectionsAsRoot() {
		$reader  = new Ini();
		$options = [
			Ini::OPTION_MODE => Ini::SECTIONS_ROOT
		];
		
		$config = $reader->parse(file_get_contents(__DIR__ . '/Ini/sections.ini'), $options);
		
		$this->assertEquals('localhost', $config['app']['host']);
		$this->assertSame(8080, $config['app']['port']);
		$this->assertSame([1, 2], $config['app']['value']);
		
		$this->assertEquals('127.0.0.1', $config['app']['db']['host']);
		$this->assertSame(1010, $config['app']['db']['port']);
		$this->assertSame([3, 4], $config['app']['srv']['value']);
		$this->assertSame([1, 2], $config['app']['owr']['value']);
		
		$this->assertEquals('/tmp', $config['test']['path']['tmp']);
		$this->assertEquals('/usr/bin', $config['test']['path']['bin']);
		
		$this->assertEquals('127.0.0.2', $config['test']['db']['host']);
		$this->assertSame(2020, $config['test']['db']['port']);
		$this->assertSame([5, 6], $config['test']['srv']['value']);
		$this->assertSame(['abc', 'zxc'], $config['test']['owr']['value']);
		
		$this->assertSame(0.11, $config['float']['float_value1']);
		$this->assertSame(0.011, $config['float']['float_value2']);
		
		$this->assertSame([5, 6], $config['test2']['value']);
		
		$this->assertEquals(1, $config['dev : app']['key1']);
		$this->assertEquals(2, $config['dev : app']['key2']);
	}
	
	public function testInheritanceRoot() {
		$reader  = new Ini();
		$options = [
			Ini::OPTION_INHERITANCE => true,
			Ini::OPTION_MODE        => Ini::SECTIONS_PICK,
			Ini::OPTION_SECTION     => 'app'
		];
		
		$config = $reader->parse(file_get_contents(__DIR__ . '/Ini/inheritance.ini'), $options);
		
		$this->assertSame(1, $config['value1']);
		$this->assertSame(0, $config['development']);
		$this->assertSame([12, 13], $config['dbs']);
		$this->assertSame([['host' => '127.0.0.1']], $config['srv']);
		
		$this->assertArrayNotHasKey('value2', $config);
		$this->assertArrayNotHasKey('value3', $config);
	}
	
	public function testInheritanceExtend() {
		$reader  = new Ini();
		$options = [
			Ini::OPTION_INHERITANCE => true,
			Ini::OPTION_MODE        => Ini::SECTIONS_PICK,
			Ini::OPTION_SECTION     => 'dev'
		];
		
		$config = $reader->parse(file_get_contents(__DIR__ . '/Ini/inheritance.ini'), $options);
		
		$this->assertSame(1, $config['value1']);
		$this->assertSame(2, $config['value2']);
		$this->assertSame(1, $config['development']);
		$this->assertSame([14, 15], $config['dbs']);
		$this->assertSame([
			['host' => '127.0.0.1'],
			['host' => '127.0.0.2']
		], $config['srv']);
		
		$this->assertArrayNotHasKey('value3', $config);
	}
	
	public function testInheritanceExtendChain() {
		$reader  = new Ini();
		$options = [
			Ini::OPTION_INHERITANCE => true,
			Ini::OPTION_MODE        => Ini::SECTIONS_PICK,
			Ini::OPTION_SECTION     => 'test2'
		];
		
		$config = $reader->parse(file_get_contents(__DIR__ . '/Ini/inheritance.ini'), $options);
		
		$this->assertSame(1, $config['value1']);
		$this->assertSame(2, $config['value2']);
		$this->assertSame(3, $config['value3']);
		$this->assertSame(2, $config['development']);
		$this->assertSame([16, 17], $config['dbs']);
		$this->assertSame([
			['host' => '127.0.0.1'],
			['host' => '127.0.0.2'],
			['host' => '127.0.0.3']
		], $config['srv']);
	}
	
	public function testInvalidSection() {
		$reader  = new Ini();
		$options = [
			Ini::OPTION_MODE    => Ini::SECTIONS_PICK,
			Ini::OPTION_SECTION => 'test'
		];
		
		$this->setExpectedException('ZExt\Config\Reader\Exceptions\InvalidIniSection');
		
		$reader->parse('[app]', $options);
	}
	
	public function testInvalidSectionDefinition() {
		$reader  = new Ini();
		$options = [
			Ini::OPTION_INHERITANCE => true,
			Ini::OPTION_MODE        => Ini::SECTIONS_PICK,
			Ini::OPTION_SECTION     => 'test'
		];
		
		$this->setExpectedException('ZExt\Config\Reader\Exceptions\InvalidIniSection');
		
		$reader->parse('[:]', $options);
	}
	
	public function testInvalidKeyDefinition() {
		$reader  = new Ini();
		$options = [
			Ini::OPTION_MODE => Ini::SECTIONS_IGNORE
		];
		
		$this->setExpectedException('ZExt\Config\Reader\Exceptions\InvalidIniKey');
		
		$reader->parse('path..tmp = /tmp', $options);
	}
	
}