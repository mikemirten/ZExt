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
 * @version   1.1
 */

namespace ZExt\Model;

use ZExt\Di\LocatorAwareInterface,
    ZExt\Di\LocatorAwareTrait;

use ZExt\Log\LoggerAwareInterface,
    ZExt\Log\LoggerAwareTrait;

use ZExt\Events\EventsManagerAwareInterface,
    ZExt\Events\EventsManagerAwareTrait;

use ZExt\Dump\Html as DumpDecorator;

/**
 * Models' abstract class
 * 
 * @category   ZExt
 * @package    Model
 * @subpackage ModelAbstract
 * @author     Mike.Mirten
 * @version    2.0.1
 */
abstract class ModelAbstract implements ModelInterface, LocatorAwareInterface, LoggerAwareInterface, EventsManagerAwareInterface {
	
	use ParentsAwareTrait;
	use LocatorAwareTrait;
	use LoggerAwareTrait;
	use EventsManagerAwareTrait;
	
	/**
	 * Model's data
	 * 
	 * @var array
	 */
	protected $_data = [];
	
	/**
	 * Model's metadata
	 * 
	 * @var Model
	 */
	protected $_meta;
	
	/**
	 * Dump decorator
	 *
	 * @var object
	 */
	protected $_dumpDecorator;
	
	/**
	 * For extensions
	 */
	protected function init(){}
	
	/**
	 * Set model's data
	 * 
	 * @param array $data
	 * @return ModelAbstract
	 */
	public function setData(array $data) {
		$this->_data = $data;
		
		return $this;
	}
	
	/**
	 * Set linked model's data
	 * 
	 * @param array $data
	 * @return ModelAbstract
	 */
	public function setDataLinked(array &$data) {
		$this->_data = &$data;
		
		return $this;
	}
	
	/**
	 * Get model's data
	 * 
	 * @return array
	 */
	public function getData() {
		return $this->_data;
	}
	
	/**
	 * Get linked model's data
	 * 
	 * @return array
	 */
	public function &getDataLinked() {
		return $this->_data;
	}
	
	/**
	 * Get data as array (alias to getData())
	 * 
	 * @return array
	 */
	public function toArray() {
		return $this->getData();
	}
	
	/**
	 * Model is empty check
	 * 
	 * @return bool
	 */
	public function isEmpty() {
		return empty($this->_data);
	}
	
	/**
	 * Get model's metadata
	 * 
	 * @return Model
	 */
	public function getMetadata() {
		if ($this->_meta === null) {
			$this->_meta = new Model();
		}
		
		return $this->_meta;
	}
	
	/**
	 * Model has metadata
	 * 
	 * @return bool
	 */
	public function hasMetadata() {
		return $this->_meta !== null && ! $this->_meta->isEmpty();
	}
	
	/**
	 * Get a dump decorator
	 * 
	 * @return object
	 */
	public function getDumpDecorator() {
		if ($this->_dumpDecorator === null) {
			if ($this->hasLocator() && $this->getLocator()->has('modelsDumpDecorator')) {
				$this->_dumpDecorator = $this->getLocator()->get('modelsDumpDecorator');
			} else {
				$this->_dumpDecorator = new DumpDecorator();
			}
		}
		
		return $this->_dumpDecorator;
	}
	
	/**
	 * Set a dump decorator
	 * 
	 * @param  object $decorator
	 * @return ModelAbstract
	 */
	public function setDumpDecorator() {
		
	}
	
	/**
	 * Get the dump of the model's condition and the data
	 * 
	 * @param  int $recursion
	 * @return mixed
	 */
	public function dump($recursion = 4) {
		return $this->getDumpDecorator()->dump($this, $recursion);
	}
	
	public function __sleep() {
		return [
			'_data',
			'_meta',
			'_forceInsert'
		];
	}
	
	public function __wakeup() {
		
	}
	
}