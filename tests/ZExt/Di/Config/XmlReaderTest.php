<?php

use ZExt\Di\Config\XmlReader;
use ZExt\Filesystem\File;

class XmlReaderTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Includes
	 *
	 * @var array
	 */
	protected static $includes;
	
	/**
	 * Parameters
	 *
	 * @var array
	 */
	protected static $parameters;
	
	/**
	 * Services
	 *
	 * @var object
	 */
	protected static $services;
	
	/**
	 * Initializers
	 *
	 * @var object
	 */
	protected static $initializers;
	
	public static function setUpBeforeClass() {
		$reader = new XmlReader(new File(__DIR__ . DIRECTORY_SEPARATOR . 'config.xml'));
		
		self::$includes     = $reader->getIncludes();
		self::$parameters   = $reader->getParameters();
		self::$services     = $reader->getServices();
		self::$initializers = $reader->getInitializers();
	}
	
	public function testService() {
		$this->assertEquals((object) [
			'type'  => 'class',
			'class' => 'TestClass'
		], self::$services->service);
	}
	
	public function testServiceNamespace() {
		$this->assertEquals((object) [
			'type'  => 'class',
			'class' => 'Library\TestClass'
		], self::$services->serviceNamespace);
	}
	
	public function testServiceFactory() {
		$this->assertTrue(self::$services->serviceFactory->factory);
	}
	
	public function testServiceArgBoolean() {
		$this->assertEquals([
			(object) ['type' => 'value', 'value' => true],
			(object) ['type' => 'value', 'value' => false]
		], self::$services->serviceArgBoolean->arguments);
	}
	
	public function testServiceArgInteger() {
		$this->assertEquals([
			(object) ['type' => 'value', 'value' => 65535],
			(object) ['type' => 'value', 'value' => 255]
		], self::$services->serviceArgInteger->arguments);
	}
	
	public function testServiceArgString() {
		$this->assertEquals([
			(object) ['type' => 'value', 'value' => 'qwerty'],
			(object) ['type' => 'value', 'value' => 'asdfgh']
		], self::$services->serviceArgString->arguments);
	}
	
	public function testServiceArgNull() {
		$this->assertEquals([
			(object) ['type' => 'value', 'value' => null],
			(object) ['type' => 'value', 'value' => null]
		], self::$services->serviceArgNull->arguments);
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
		], self::$services->serviceArgArray->arguments);
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
		], self::$services->serviceArgArrayRecursive->arguments);
	}
	
	public function testServiceArgService() {
		$this->assertEquals([
			(object) ['type' => 'service', 'id' => 'service1'],
			(object) ['type' => 'service', 'id' => 'service2']
		], self::$services->serviceArgService->arguments);
	}
	
	public function testServiceArgArrayService() {
		$this->assertEquals([
			(object) ['type' => 'value', 'value' => [
				(object) ['type' => 'service', 'id' => 'service1'],
				(object) ['type' => 'service', 'id' => 'service2']
			]]
		], self::$services->serviceArgArrayService->arguments);
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
		], self::$services->serviceArgServiceWithArgs->arguments);
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
		], self::$services->serviceArgArrayServiceWithArgs->arguments);
	}
	
	public function testInitializerNamespace() {
		$this->assertEquals((object) [
			'type'      => 'namespace',
			'namespace' => 'Forms'
		], self::$initializers->forms);
	}
	
	public function testInitializerObject() {
		$this->assertEquals((object) [
			'type'  => 'object',
			'class' => 'AppInitializer'
		], self::$initializers->app);
	}
	
	public function testInitializerFactory() {
		$this->assertTrue(self::$initializers->tags->factory);
	}
	
	public function testInitializerWithArgs() {
		$this->assertEquals([
			(object) ['type' => 'service', 'id' => 'adapter'],
			(object) ['type' => 'value', 'value' => 'qwerty']
		], self::$initializers->models->arguments);
	}
	
	public function testIncludes() {
		$this->assertEquals(['acl.xml', 'config.xml'], self::$includes);
	}
	
	public function testParameters() {
		$this->assertEquals((object) [
			'type'  => 'value',
			'value' => 1000
		], self::$parameters->param1);
		
		$this->assertEquals((object) [
			'type'  => 'service',
			'id'    => 'cache'
		], self::$parameters->param2);
		
		$this->assertEquals((object) [
			'type'  => 'value',
			'value' => 'qwerty'
		], self::$parameters->param3);
	}
	
}
