<?php

use ZExt\Cache\Backend\BackendInterface;

class BackendTestCase implements BackendInterface {
	
	/**
	 * Test data
	 * 
	 * @var array
	 */
	protected $data;
	
	/**
	 * Constructor
	 * 
	 * @param array $data
	 */
	public function __construct(array $data = []) {
		$this->data = $data;
	}
	
	/**
	 * Fetch the data from the cache
	 * 
	 * @param  string $id
	 * @return mixed | null if no data
	 */
	public function get($id) {
		if (isset($this->data[$id])) {
			return $this->data[$id];
		}
	}
	
	/**
	 * Fetch the many of the data from the cache
	 * 
	 * @param  array $ids
	 * @return array
	 */
	public function getMany(array $ids) {
		return array_intersect_key($this->data, array_flip($ids));
	}
	
	/**
	 * Store the data into the cache
	 * 
	 * @param  string $id       ID of the stored data
	 * @param  mixed  $data     Stored data
	 * @param  int    $lifetime Lifetime in seconds
	 * @return bool
	 */
	public function set($id, $data, $lifetime = 0) {
		$this->data[$id] = $data;
		
		return true;
	}
	
	/**
	 * Store the many of the date into the cache
	 * 
	 * @param  array $data
	 * @param  int   $lifetime
	 * @return bool
	 */
	public function setMany(array $data, $lifetime = 0) {
		$this->data = $data + $this->data;
		
		return true;
	}
	
	/**
	 * Check whether the data exists in the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function has($id) {
		return isset($this->data[$id]);
	}
	
	/**
	 * Remove the data from the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function remove($id) {
		unset($this->data[$id]);
		
		return true;
	}
	
	/**
	 * Remove the many the data from the cache
	 * 
	 * @param  array $ids
	 * @return bool
	 */
	public function removeMany(array $ids) {
		foreach ($ids as $id) {
			unset($this->data[$id]);
		}
		
		return true;
	}
	
	/**
	 * Increment the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 */
	public function inc($id, $value = 1) {
		if (isset($this->data[$id]) && is_numeric($this->data[$id])) {
			$this->data[$id] += $value;
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Decrement the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 */
	public function dec($id, $value = 1) {
		if (isset($this->data[$id]) && is_numeric($this->data[$id])) {
			$this->data[$id] -= $value;
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Flush all the cache data
	 * 
	 * @return bool
	 */
	public function flush() {
		$this->data = [];
		
		return true;
	}
	
}