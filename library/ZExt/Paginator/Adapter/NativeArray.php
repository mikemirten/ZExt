<?php
/**
 * ZExt Framework (http://z-ext.com)
 * Copyright (C) 2012 Mike.Mirten
 * 
 * LICENSE
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * 
 * @copyright (c) 2012, Mike.Mirten
 * @license   http://www.gnu.org/licenses/gpl.html GPL License
 * @category  ZExt
 * @version   1.0
 */

namespace ZExt\Paginator\Adapter;

use ZExt\Model\Collection;
use ArrayIterator, RuntimeException;

/**
 * Native array data adapter
 * 
 * @category   ZExt
 * @package    Paginator
 * @subpackage Adapter
 * @author     Mike.Mirten
 * @version    1.0
 */
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