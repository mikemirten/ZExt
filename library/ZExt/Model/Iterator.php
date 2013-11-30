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

namespace ZExt\Model;

use ZExt\Di\LocatorAwareInterface,
    ZExt\Di\LocatorAwareTrait;

use Iterator  as IteratorInterface,
    Countable as CountableInterface,
    ReflectionClass;

/**
 * Iterator
 * 
 * @category   ZExt
 * @package    Model
 * @subpackage Iterator
 * @author     Mike.Mirten
 * @version    1.0.1
 */
class Iterator implements IteratorInterface, CountableInterface, LocatorAwareInterface {
	
	use LocatorAwareTrait;
	use ParentsAwareTrait;
	
	const PRIMARY_MODEL = 'ZExt\Model\Model';
	
	/**
	 * Iterator's metadata
	 * 
	 * @var Model
	 */
	protected $_meta;
	
	/**
	 * Models' class
	 * 
	 * @var type 
	 */
	protected $_modelClass = self::PRIMARY_MODEL;
	
	/**
	 * Inner iterator
	 *
	 * @var IteratorInterface
	 */
	protected $_iterator;
	
	/**
	 * Iterators' factory
	 * 
	 * @param  IteratorInterface $iterator
	 * @param  string $model
	 * @return Iterator
	 */
	public static function factory(IteratorInterface $iterator, $model = null) {
		return new static($iterator, $model);
	}
	
	/**
	 * Constructor
	 * 
	 * @param IteratorInterface $iterator
	 * @param string $model
	 */
	public function __construct(IteratorInterface $iterator, $model = null) {
		$this->setIterator($iterator);
		
		if ($model !== null) {
			$this->setModel($model);
		}
	}
	
	/**
	 * Set an iterator
	 * 
	 * @param  IteratorInterface $iterator
	 * @return Iterator
	 */
	public function setIterator(IteratorInterface $iterator) {
		$this->_iterator = $iterator;
		
		return $this;
	}
	
	/**
	 * Get an iterator
	 * 
	 * @return Iterator
	 */
	public function getIterator() {
		return $this->_iterator;
	}
	
	/**
	 * Set a class of a models
	 *
	 * @param  string $modelClass
	 * @throws Exception
	 * @return Iterator
	 */
	public function setModel($modelClass) {
		$reflection = new ReflectionClass($modelClass);
		
		if (! $reflection->implementsInterface('ZExt\Model\ModelInterface')) {
			throw new Exception('Class of a model must implements "ModelInterface"');
		}
		
		$this->_modelClass = $modelClass;
		
		return $this;
	}
	
	/**
	 * Get a class of a models
	 *
	 * @return string
	 */
	public function getModel() {
		return $this->_modelClass;
	}
	
	/**
	 * Get a name of a models
	 * 
	 * @return string
	 */
	public function getName() {
		$pos = strrpos($this->_modelClass, '\\');
		
		return substr($this->_modelClass, $pos + 1);
	}
	
	/**
	 * Create a model
	 * 
	 * @param  array $data
	 * @return Model
	 */
	protected function createModel($data) {
		$modelClass = $this->getModel();
			
		$model = $modelClass::factory($data);

		$datagate = $this->getParentDatagate();
		if ($datagate !== null) {
			$model->setParentDatagate($datagate);
		}
		
		if ($this->hasLocator()) {
			$model->setLocator($this->getLocator());
		}
		
		return $model;
	}
	
	/**
	 * Get an iterator's data as an array
	 * 
	 * @param  bool $recursively Models to arrays also
	 * @return array
	 */
	public function toArray($recursively = false) {
		if ($recursively) {
			return iterator_to_array($this->_iterator);
		} else {
			$models = array();
			
			foreach ($this->_iterator as $data) {
				$models[] = $this->createModel($data);
			}
			
			return $models;
		}
	}
	
	/**
	 * Get an iterator'a data as a json encoded string
	 * 
	 * @return string 
	 */
	public function toJson() {
		return json_encode(iterator_to_array($this->_iterator));
	}
	
	/**
	 * Get an iterator's data as Collection
	 * 
	 * @return Collection
	 */
	public function toCollection() {
		$data  = iterator_to_array($this->_iterator);
		$model = $this->getModel();
		
		$collection = new Collection($data, $model);

		$datagate = $this->getParentDatagate();
		if ($datagate !== null) {
			$collection->setParentDatagate($datagate);
		}
		
		if ($this->hasLocator()) {
			$collection->setLocator($this->getLocator());
		}
		
		return $collection;
	}
	
	/**
	 * Get the iterator's metadata
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
	 * Iterator has the metadata
	 * 
	 * @return bool
	 */
	public function hasMetadata() {
		return $this->_meta !== null && ! $this->_meta->isEmpty();
	}
	
	/**
	 * Is the iterator empty
	 * 
	 * @return bool
	 */
	public function isEmpty() {
		return $this->_iterator->count() === 0;
	}

	/**
	 * Get the dump of the iterator's condition
	 * 
	 * @param  int $recursion
	 * @return mixed
	 */
	public function dump($recursion = 4) {
		if ($this->hasLocator() && $this->getLocator()->has('modelsDumpDecorator')) {
			$decorator = $this->getLocator()->get('modelsDumpDecorator');
		} else {
			throw new Exception('Dump\'s decorator wasn\'t supplied');
		}
		
		return $decorator->dump($this, $recursion);
	}
	
	// Iterator interface
	
	public function current() {
		return $this->createModel($this->_iterator->current());
	}
	
	public function key() {
		return $this->_iterator->key();
	}
	
	public function next() {
		return $this->_iterator->next();
	}
	
	public function rewind() {
		return $this->_iterator->rewind();
	}
	
	public function valid() {
		return $this->_iterator->valid();
	}
	
	// Countable interface
	
	public function count() {
		return $this->_iterator->count();
	}
	
}