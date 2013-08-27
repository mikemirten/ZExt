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

use IteratorAggregate, ArrayIterator;

/**
 * Model
 * 
 * @category   ZExt
 * @package    Model
 * @subpackage Model
 * @author     Mike.Mirten
 * @version    2.1
 */
class Model extends ModelAbstract implements IteratorAggregate {
	
	const INIT_POSTFIX = 'Init';
	
	const SOURCE_METHOD  = 'method';
	const SOURCE_MODE    = 'mode';
	const SOURCE_PRIMARY = 'sourcePrimary';
	
	const MODE_JOIN         = 1;
	const MODE_CASCADE      = 2;
	const MODE_CASCADE_MANY = 3;
	
	/**
	 * Sources list for lazy data initialization
	 *
	 * @var array
	 */
	private $_sources;
	
	/**
	 * Names of a resources which been initialized
	 *
	 * @var array 
	 */
	protected $_initialized = array();
	
	/**
	 * Parental collection of an object
	 *
	 * @var Collection 
	 */
	protected $_parentCollection;
	
	/**
	 * Items' factory
	 * 
	 * @param  array $data
	 * @return Model
	 */
	public static function factory(array &$data = null) {
		return new static($data);
	}
	
	/**
	 * Items's constructor
	 * 
	 * @param array $data
	 */
	public function __construct(array &$data = null, $dataLinked = true) {
		if ($data !== null) {
			if ($dataLinked === true) {
				$this->setDataLinked($data);
			} else {
				$this->setData($data);
			}
		}
		
		$this->_sources = $this->getLazyLoadSources();
		
		if ($this->_sources !== null) {
			$initialized = array_intersect_key($this->_sources, $this->_data);
			$this->_initialized = array_fill_keys(array_keys($initialized), true);
		}
		
		$this->init();
	}
	
	/**
	 * Merge data of an object into this object
	 * 
	 * @param  Item $object
	 * @return Model
	 */
	public function merge(Model $object) {
		$this->_data = $object->toArray() + $this->_data;
		
		return $this;
	}
	
	/**
	 * Set model's values
	 * 
	 * @param array $values
	 * @return Model
	 */
	public function setValues(array $values) {
		$this->_data = $values + $this->_data;
		
		return $this;
	}
	
	/**
	 * Change existing model's values
	 * 
	 * @param  array $values
	 * @return Model
	 */
	public function changeValues(array $values) {
		$values = array_intersect_key($values, $this->_data);
		
		$this->_data = $values + $this->_data;
		
		return $this;
	}
	
	/**
	 * Set a parental collection
	 * 
	 * @param  Collection $collection
	 * @return Model
	 */
	public function setCollection(Collection $collection) {
		$this->_parentCollection = $collection;
		
		return $this;
	}
	
	/**
	 * Get a parental collection
	 * 
	 * @return Collection
	 */
	public function getCollection() {
		return $this->_parentCollection;
	}
	
	/**
	 * Has a madel parental collection
	 * 
	 * @return bool
	 */
	public function hasCollection() {
		return $this->_parentCollection !== null;
	}
	
	/**
	 * Unlink the parental collection
	 * 
	 * @return Model
	 */
	public function unlinkCollection() {
		$this->_parentCollection = null;
		
		return $this;
	}

	/**
	 * Get a name of a model
	 * 
	 * @return string
	 */
	public function getName() {
		$class = get_class($this);
		
		return substr($class, strrpos($class, '\\') + 1);
	}
	
	/**
	 * List of sources of data for lazy loading
	 * 
	 * parameter => responsible method
	 * 
	 * @return array
	 */
	protected function getLazyLoadSources(){}
	
	/**
	 * Initialize data
	 * 
	 * @param  string | array $property
	 * @return Model
	 */
	public function initialize($property = null) {
		// By method
		$initMethod = lcfirst($property) . self::INIT_POSTFIX;
		
		if (method_exists($this, $initMethod)) {
			$this->_data[$property]        = $this->$initMethod();
			$this->_initialized[$property] = true;
			
			return $this;
		}
		
		// By source definition
		if ($this->_sources === null) {
			return $this;
		}
		
		if ($property === null) {
			$properties = array_keys($this->_sources);
		} else {
			$properties = array($property);
		}
		
		foreach ($properties as $property) {
			if (isset($this->_initialized[$property])) continue;

			if (isset($this->_sources[$property])) {
				$this->_initialize($property, $this->_sources[$property]);
				$this->_initialized[$property] = true;
			}
		}
		
		return $this;
	}
	
	/**
	 * Remove initialized data
	 * 
	 * @return Model
	 */
	public function uninitialize() {
		if (! empty($this->_initialized)) {
			foreach (array_keys($this->_initialized) as $property) {
				unset($this->_data[$property]);
			}
		}
		
		return $this;
	}

	/**
	 * Lazy initialization of data
	 * 
	 * @param  string $method
	 * @throws Exception
	 */
	protected function _initialize($property, $method) {
		if (is_string($method)) {
			$mode = self::MODE_JOIN;
			$sourcePrimary = null;
		}
		elseif (is_array($method)) {
			if (isset($method[self::SOURCE_METHOD], $method[self::SOURCE_MODE])) {
				$sourcePrimary = isset($method[self::SOURCE_PRIMARY]) ? $method[self::SOURCE_PRIMARY] : null;
				
				$mode   = $method[self::SOURCE_MODE];
				$method = $method[self::SOURCE_METHOD];
			}
			elseif (isset($method[0], $method[1])) {
				$sourcePrimary = isset($method[2]) ? $method[2] : null;
				
				$mode   = $method[1];
				$method = $method[0];
			}
			else {
				throw new Exception('Invalid source definition');
			}
		}
		else {
			throw new Exception('Source must be specified as a string or an array');
		}
		
		if ($this->hasCollection()) {
			$this->_initMany($property, $method, $mode, $sourcePrimary);
		} else {
			$this->_initOne($property, $method, $mode);
		}
	}
	
	/**
	 * Get a data for this model
	 * 
	 * @param  string $property
	 * @param  string $method
	 * @param  int    $mode
	 * @throws Exception
	 */
	private function _initOne($property, $method, $mode) {
		$data = $this->__call($method);
		if ($data === null) return;

		if ($data instanceof Collection) {
			if ($mode !== self::MODE_CASCADE_MANY) {
				throw new Exception('Model hasn\'t a parental collection');
			}

			$this->_data[$property] = $data;

			return;
		}

		if ($mode === self::MODE_JOIN) {
			if ($data instanceof Model) {
				$data = $data->toArray();
			}

			if (! is_array($data)) {
				throw new Exception('Unsupported type "' . gettype($data) . '" provided by "' . $method . '()"');
			}

			$this->_data += $data;
		}
		else if ($mode === self::MODE_CASCADE) {
			$this->_data[$property] = $data;
		}
		else if ($mode === self::MODE_CASCADE_MANY) {
			throw new Exception('Unable to use a model in the "Cascade many" mode');
		}
		else {
			throw new Exception('Uncnown loading mode');
		}
	}
	
	/**
	 * Get a data for all models in a collection
	 * 
	 * @param  string $property
	 * @param  string $method
	 * @param  int    $mode
	 * @param  string $sourcePrimary
	 * @throws Exception
	 */
	private function _initMany($property, $method, $mode, $sourcePrimary) {
		$collection = $this->getCollection();
		
		$data = $collection->__call($method);
		if ($data === null) return;

		if ($data instanceof Collection) {
			if ($data->hasMetadata()) {
				$meta = $data->getMetadata();

				$onProperty     = $meta->joinOnProperty;
				$sourceProperty = $meta->joinOnSource;
			} else {
				$onProperty     = null;
				$sourceProperty = null;
			}

			if ($mode === self::MODE_CASCADE) {
				// If not specified in meta, using a requested property
				if ($onProperty === null) {
					$onProperty = $property;
				}

				// If not specified in meta:
				if ($sourceProperty === null) {
					if ($sourcePrimary === null) {
						// Attempt to get from source collection
						$sourceProperty = $data->getPrimary();

						if ($sourceProperty === null) {
							throw new Exception('Unable to determine a source property for join');
						}
					} else {
						// Manually specified
						$sourceProperty = $sourcePrimary;
					}
				}
			}

			if ($mode === self::MODE_JOIN) {
				$collection->join($data, $onProperty, $sourceProperty);
			}
			else if ($mode === self::MODE_CASCADE || $mode === self::MODE_CASCADE_MANY) {
				$collection->setList($onProperty, $data->toArray(), $sourceProperty);
			}
			else {
				throw new Exception('Uncnown loading mode');
			}
		} else {
			throw new Exception('Data must be represented as a collection');
		}
	}
	
	/**
	 * Get the data iterator
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return new ArrayIterator($this->_data);
	}
	
	/**
	 * Count a number of a data items
	 * 
	 * @return int
	 */
	public function count() {
		return count($this->_data);
	}
	
	/**
	 * Set the value of the property
	 * 
	 * @param string $name
	 * @param mixed  $value
	 */
	public function __set($name, $value) {
		$this->_data[$name] = $value;
	}
	
	/**
	 * Get the value of the property
	 * 
	 * @param type $name
	 * @return type
	 */
	public function __get($name) {
		if (isset($this->_data[$name])) {
			return $this->_data[$name];
		}
		
		$this->initialize($name);
		
		// Second attempt
		if (isset($this->_data[$name])) {
			return $this->_data[$name];
		}
	}
	
	/**
	 * Is the property exists
	 * 
	 * @param  string $name
	 * @return bool
	 */
	public function __isset($name) {
		// Property exists
		if (isset($this->_data[$name])) {
			return true;
		}
		
		// Init method exists
		if (method_exists($this, lcfirst($name) . self::INIT_POSTFIX)) {
			return true;
		}
		
		// Init source exists
		if (! empty($this->_sources[$name])) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Unset the property
	 * 
	 * @param type $name
	 */
	public function __unset($name) {
		unset($this->_data[$name]);
	}
	
	public function __sleep() {
		$this->uninitialize();
		
		return parent::__sleep();
	}
	
	public function __clone() {
		$data = $this->_data;
		$this->_data = &$data;
	}
	
}