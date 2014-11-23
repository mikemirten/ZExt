<?php

use ZExt\Config\Config;

class ConfigTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @dataProvider configSourceProvider
	 */
	public function testConfigGet($source) {
		$config = new Config($source);
		
		$this->assertSame(1, $config->get('item1'));
		$this->assertSame(2, $config->get('item2'));
		
		$this->assertSame(3, $config->get('section.item3'));
		$this->assertSame(4, $config->get('section.item4'));
		
		$this->assertNull($config->nonExists);
	}
	
	/**
	 * @dataProvider configSourceProvider
	 */
	public function testConfigGetMagic($source) {
		$config = new Config($source);
		
		$this->assertSame(1, $config->item1);
		$this->assertSame(2, $config->item2);
		
		$this->assertSame(3, $config->section->item3);
		$this->assertSame(4, $config->section->item4);
		
		$this->assertNull($config->nonExists);
	}
	
	/**
	 * @dataProvider configSourceProvider
	 */
	public function testConfigHas($source) {
		$config = new Config($source);
		
		$this->assertFalse($config->has('item0'));
		$this->assertFalse($config->has('section.item0'));
		
		$this->assertTrue($config->has('item1'));
		$this->assertTrue($config->has('section.item3'));
	}
	
	/**
	 * @dataProvider configSourceProvider
	 */
	public function testConfigIsset($source) {
		$config = new Config($source);
		
		$this->assertFalse(isset($config->item0));
		$this->assertFalse(isset($config->section->item0));
		
		$this->assertTrue(isset($config->item1));
		$this->assertTrue(isset($config->section->item3));
	}
	
	public function testConfigReadOnly() {
		$config = new Config();
		$this->assertFalse($config->isLocked());
		
		$config = new Config();
		$config->lock();
		$this->assertTrue($config->isLocked());
		
		$config = new Config(['item' => 1], false);
		$this->assertFalse($config->isLocked());
		
		$config = new Config(['item' => 1]);
		$this->assertTrue($config->isLocked());
	}
	
	/**
	 * @expectedException ZExt\Config\Exceptions\ReadOnly
	 */
	public function testConfigReadOnlyException() {
		$config = new Config(['item' => 1]);
		$config->item = 1;
	}
	
	public function testConfigSet() {
		$config = new Config();
		
		$config->set('item1', 1);
		$config->set('item2', 2);
		
		$this->assertSame(1, $config->item1);
		$this->assertSame(2, $config->item2);
		
		$config->set('section', [
			'item3' => 3,
			'item4' => 4
		]);
		
		$config->set('section.item5', 5);
		
		$this->assertSame(3, $config->section->item3);
		$this->assertSame(4, $config->section->item4);
		$this->assertSame(5, $config->section->item5);
	}
	
	public function testConfigSetMagic() {
		$config = new Config();
		
		$config->item1 = 1;
		$config->item2 = 2;
		
		$this->assertSame(1, $config->item1);
		$this->assertSame(2, $config->item2);
		
		$config->section = [
			'item3' => 3,
			'item4' => 4,
		];
				
		$this->assertSame(3, $config->section->item3);
		$this->assertSame(4, $config->section->item4);
	}
	
	/**
	 * @dataProvider configSourceProvider
	 */
	public function testConfigRemove($source) {
		$config = new Config($source, false);
		
		$config->remove('item1');
		$config->remove('item2');
		$config->remove('section.item3');
		
		$this->assertFalse(isset($config->item1));
		$this->assertFalse(isset($config->item2));
		
		$this->assertFalse(isset($config->section->item3));
		$this->assertTrue(isset($config->section->item4));
	}
	
	/**
	 * @dataProvider configSourceProvider
	 */
	public function testConfigUnset($source) {
		$config = new Config($source, false);
		
		unset($config->item1, $config->item2, $config->section);
		
		$this->assertFalse(isset($config->item1));
		$this->assertFalse(isset($config->item2));
		$this->assertFalse(isset($config->section->item3));
	}
	
	/**
	 * @dataProvider configSourceProvider
	 */
	public function testConfigMerge($source1, $source2) {
		$config1 = new Config($source1, false);
		$config2 = new Config($source2);
		
		$this->assertFalse($config1->merge);
		$this->assertFalse($config1->section->merge);
		
		$config1->merge($config2);
		
		$this->assertSame(1,  $config1->item1);
		$this->assertSame(10, $config1->item10);
		
		$this->assertSame(3,  $config1->section->item3);
		$this->assertSame(30, $config1->section->item30);
		
		$this->assertTrue($config1->merge);
		$this->assertTrue($config1->section->merge);
	}
	
	/**
	 * @dataProvider configSourceProvider
	 */
	public function testConfigToArray($source) {
		$config = new Config($source);
		
		$this->assertSame($source, $config->toArray());
	}
	
	public function testConfigToFlatArray() {
		$config = new Config([
			'item1'   => 1,
			'section' => [
				'item2'      => 2,
				'subsection' => [
					'item3' => 3
				]
			]
		]);
		
		$this->assertSame([
			'item1'                    => 1,
			'section.item2'            => 2,
			'section.subsection.item3' => 3
		], $config->toFlatArray());
	}
	
	/**
	 * @dataProvider configSourceProvider
	 */
	public function testConfigClone($source) {
		$config = new Config($source);
		
		$clonedConfig = clone $config;
		
		$clonedConfig->item1          = 100;
		$clonedConfig->section->item3 = 300;
		$clonedConfig->section->item5 = 500;
		
		$this->assertFalse($clonedConfig->isLocked());
		
		$this->assertSame(1, $config->item1);
		$this->assertSame(3, $config->section->item3);
		$this->assertNull($config->section->item5);
	}
	
	public function configSourceProvider() {
		return [
			[
				[
					'item1' => 1,
					'item2' => 2,
					'section' => [
						'item3' => 3,
						'item4' => 4,
						'merge' => false
					],
					'merge' => false
				],
				[
					'item10' => 10,
					'item20' => 20,
					'section' => [
						'item30' => 30,
						'item40' => 40,
						'merge'  => true
					],
					'merge' => true
				]
			]
		];
	}
	
}