<?php

use ZExt\Di\Config\XmlReader;

class XmlReaderTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Test config
	 *
	 * @var object
	 */
	protected static $config;
	
	public static function setUpBeforeClass() {
		$reader = new XmlReader(__DIR__ . DIRECTORY_SEPARATOR . 'config.xml');
		
		self::$config = $reader->getConfiguration();
	}
	
	public function testService() {
		$this->assertEquals((object) [
			'type'  => 'class',
			'class' => 'TestClass'
		], self::$config->services->service);
	}
	
	public function testServiceNamespace() {
		$this->assertEquals((object) [
			'type'  => 'class',
			'class' => 'Library\TestClass'
		], self::$config->services->serviceNamespace);
	}
	
	public function testServiceFactory() {
		$this->assertTrue(self::$config->services->serviceFactory->factory);
	}
	
	public function testServiceArgBoolean() {
		$this->assertEquals([
			(object) ['type' => 'value', 'value' => true],
			(object) ['type' => 'value', 'value' => false]
		], self::$config->services->serviceArgBoolean->arguments);
	}
	
	public function testServiceArgInteger() {
		$this->assertEquals([
			(object) ['type' => 'value', 'value' => 65535],
			(object) ['type' => 'value', 'value' => 255]
		], self::$config->services->serviceArgInteger->arguments);
	}
	
	public function testServiceArgString() {
		$this->assertEquals([
			(object) ['type' => 'value', 'value' => 'qwerty'],
			(object) ['type' => 'value', 'value' => 'asdfgh']
		], self::$config->services->serviceArgString->arguments);
	}
	
	public function testServiceArgNull() {
		$this->assertEquals([
			(object) ['type' => 'value', 'value' => null],
			(object) ['type' => 'value', 'value' => null]
		], self::$config->services->serviceArgNull->arguments);
	}
	
	public function testServiceArgArray() {
		$this->assertEquals([
			(object) ['type' => 'value', 'value' => [
				(object) ['type' => 'value', 'value' => 2],
				(object) ['type' => 'value', 'value' => 4],
				(object) ['type' => 'value', 'value' => 8]
			]],
			(object) ['type' => 'value', 'value' => [
				'x' => (object) ['type' => 'value', 'value' => 2],
				'y' => (object) ['type' => 'value', 'value' => 4],
			]],
			(object) ['type' => 'value', 'value' => [
				(object) ['type' => 'value', 'value' => 3],
				(object) ['type' => 'value', 'value' => 6],
				(object) ['type' => 'value', 'value' => 9]
			]],
			(object) ['type' => 'value', 'value' => [
				'a' => (object) ['type' => 'value', 'value' => 3],
				'b' => (object) ['type' => 'value', 'value' => 6]
			]]
		], self::$config->services->serviceArgArray->arguments);
	}
	
	public function testServiceArgArrayRecursive() {
		$this->assertEquals([
			(object) ['type' => 'value', 'value' => [
				(object) ['type' => 'value', 'value' => [
					(object) ['type' => 'value', 'value' => 2],
					(object) ['type' => 'value', 'value' => 4],
					(object) ['type' => 'value', 'value' => 8]
				]]
			]]
		], self::$config->services->serviceArgArrayRecursive->arguments);
	}
	
	public function testServiceArgService() {
		$this->assertEquals([
			(object) ['type' => 'service', 'id' => 'service1'],
			(object) ['type' => 'service', 'id' => 'service2']
		], self::$config->services->serviceArgService->arguments);
	}
	
	public function testServiceArgArrayService() {
		$this->assertEquals([
			(object) ['type' => 'value', 'value' => [
				(object) ['type' => 'service', 'id' => 'service1'],
				(object) ['type' => 'service', 'id' => 'service2']
			]]
		], self::$config->services->serviceArgArrayService->arguments);
	}
	
	public function testServiceArgServiceWithArgs() {
		$this->assertEquals([
			(object) [
				'type'      => 'service',
				'id'        => 'service',
				'arguments' => [
					(object) ['type' => 'value', 'value' => 255],
					(object) ['type' => 'value', 'value' => 'qwerty']
				]
			]
		], self::$config->services->serviceArgServiceWithArgs->arguments);
	}
	
	public function testServiceArgArrayServiceWithArgs() {
		$this->assertEquals([
			(object) ['type' => 'value', 'value' => [
				(object) [
					'type'      => 'service',
					'id'        => 'service1',
					'arguments' => [
						(object) ['type' => 'value', 'value' => 255],
						(object) ['type' => 'value', 'value' => 'qwerty']
					]
				]
			]]
		], self::$config->services->serviceArgArrayServiceWithArgs->arguments);
	}
	
	public function testInitializerNamespace() {
		$this->assertEquals((object) [
			'type'      => 'namespace',
			'namespace' => 'Forms'
		], self::$config->initializers->forms);
	}
	
	public function testInitializerObject() {
		$this->assertEquals((object) [
			'type'  => 'object',
			'class' => 'AppInitializer'
		], self::$config->initializers->app);
	}
	
	public function testInitializerFactory() {
		$this->assertTrue(self::$config->initializers->tags->factory);
	}
	
	public function testInitializerWithArgs() {
		$this->assertEquals([
			(object) ['type' => 'service', 'id' => 'adapter'],
			(object) ['type' => 'value', 'value' => 'qwerty']
		], self::$config->initializers->models->arguments);
	}
	
}
