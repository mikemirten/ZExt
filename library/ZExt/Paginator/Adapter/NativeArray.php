<?php
namespace ZExt\Paginator\Adapter;

use ZExt\Model\Collection;
use ArrayIterator, RuntimeException;

class NativeArray implements AdapterInterface {
	
	/**
	 * Data
	 *
	 * @var array
	 */
	protected $_data;
	
	/**
	 * Wrap items into the collection
	 *
	 * @var type 
	 */
	protected $_wrapIntoCollection = true;
	
	/**
	 * Models' class for the collection
	 *
	 * @var string
	 */
	protected $_modelClass;
	
	/**
	 * Primary id for the collection
	 *
	 * @var string
	 */
	protected $_primary;
	
	/**
	 * Constructor
	 * 
	 * @param array $data
	 * @param bool  $linked
	 */
	public function __construct(array &$data = null, $linked = true) {
		if ($data !== null) {
			if ($linked) {
				$this->setDataLinked($data);
			} else {
				$this->setData($data);
			}
		}
	}
	
	/**
	 * Set the data
	 * 
	 * @param array $data
	 */
	public function setData(array $data) {
		$this->_data = $data;
	}
	
	/**
	 * set the data by link
	 * 
	 * @param array $data
	 */
	public function setDataLinked(array &$data) {
		$this->_data = &$data;
	}
	
	/**
	 * Wrap items into the collection
	 * 
	 * @param bool $flag
	 */
	public function setWrapIntoCollection($flag = true, $model = null, $primary = null) {
		$this->_wrapIntoCollection = (bool) $flag;
		
		if ($model !== null) {
			$this->_modelClass = (string) $model;
		}
		
		if ($primary !== null) {
			$this->_primary = (string) $primary;
		}
	}
	
	/**
	 * Get an items of the page
	 * 
	 * @param  int $offset
	 * @param  int $limit
	 * @return \Traversable
	 */
	public function getItems($offset, $limit) {
		if ($this->_data === null) {
			throw new RuntimeException('Wasn\'t data been provided');
		}
		
		$items = array_slice($this->_data, $offset, $limit);
		
		if ($this->_wrapIntoCollection) {
			return new Collection($items, $this->_modelClass, $this->_primary);
		} else {
			return new ArrayIterator($items);
		}
	}
	
	/**
	 * Get the number of the all items
	 * 
	 * @return int
	 */
	public function count() {
		if ($this->_data === null) {
			throw new RuntimeException('Wasn\'t data been provided');
		}
		
		return count($this->_data);
	}
	
}