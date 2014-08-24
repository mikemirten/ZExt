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

use ArrayAccess,
    SeekableIterator,
    Closure,
    ReflectionClass;

/**
 * Collection
 * 
 * @category   ZExt
 * @package    Model
 * @subpackage Collection
 * @author     Mike.Mirten
 * @version    2.4.1
 */
class Collection extends ModelAbstract implements ArrayAccess, SeekableIterator {
	
	const TYPE_BOOL       = 1;
	const TYPE_INT        = 2;
	const TYPE_FLOAT      = 3;
	const TYPE_DOUBLE     = 4;
	const TYPE_STRING     = 5;
	const TYPE_ARRAY      = 6;
	const TYPE_OBJECT     = 7;
	const TYPE_MODEL      = 8;
	const TYPE_COLLECTION = 9;
	
	const ITERATION_STRAIGHT = 'straight';
	const ITERATION_REVERSE  = 'reverse';
	const ITERATION_EVEN     = 'even';
	const ITERATION_ODD      = 'odd';
	
	const SORT_ASC  = 'asc';
	const SORT_DESC = 'desc';
	
	const PRIMARY_MODEL = 'ZExt\Model\Model';
	
	const COMPOSITE_ID_PROPERTY = '_collectionCompositeId';
	
	/**
	 * Unique id counter
	 *
	 * @var int
	 */
	protected static $_collectionIdCounter = 0;
	
	/**
	 * Unique collection id
	 *
	 * @var int
	 */
	protected $_collectionId;
	
	/**
	 * Models' class
	 * 
	 * @var type 
	 */
	protected $_modelClass = self::PRIMARY_MODEL;
	
	/**
	 * Primary property
	 *
	 * @var string 
	 */
	protected $_primary;
	
	/**
	 * Original primary definition
	 *
	 * @var mixed
	 */
	protected $_primaryDefinition;
	
	/**
	 * Instanced models
	 * 
	 * @var array
	 */
	protected $_models = [];
	
	/**
	 * Iterator's pointer
	 * 
	 * @var int 
	 */
	protected $_pointer;
	
	/**
	 * Iterator's map of items
	 * 
	 * @var array 
	 */
	protected $_map = [];
	
	/**
	 * Last sort definition
	 *
	 * @var array
	 */
	protected $_sortDefinition;
	
	/**
	 * Collections' factory
	 * 
	 * @param  array  $data
	 * @param  string $model
	 * @param  string $primary
	 * @return Collection
	 */
	public static function factory(array &$data = null, $model = null, $primary = null, $dataLinked = true) {
		return new static($data, $model, $primary, $dataLinked);
	}
	
	/**
	 * Constructor
	 * 
	 * @param array $data
	 * @param type $model
	 * @param type $primary
	 */
	public function __construct(array &$data = null, $model = null, $primary = null, $dataLinked = true) {
		$this->_collectionId = self::$_collectionIdCounter ++;
		
		if ($model !== null) {
			$this->setModel($model);
		}
		
		if ($data !== null) {
			if ($dataLinked === true) {
				$this->setDataLinked($data);
			} else {
				$this->setData($data);
			}
		}
		
		if ($primary !== null) {
			$this->setPrimary($primary);
		}
		
		$this->init();
	}
	
	/**
	 * Set collection's data
	 * 
	 * @param  array $data
	 * @return Collection
	 */
	public function setData(array $data) {
		$this->setDataLinked($data);
		
		return $this;
	}
	
	/**
	 * Set linked collection's data
	 * 
	 * @param  array $data
	 * @return Collection
	 */
	public function setDataLinked(array &$data) {
		$this->_data   = [];
		$this->_models = [];
		
		$modelClass = $this->getModel();
		
		foreach ($data as $key => &$item) {
			if (is_object($item)) {
				if (! $item instanceof $modelClass) {
					throw new Exception('Model must be an instance of "' . $modelClass . '"');
				}
				
				$this->_data[$key]   = &$item->getDataLinked();
				$this->_models[$key] = $item;
			}
			elseif (is_array($item)) {
				$this->_data[$key] = &$item;
			}
			else {
				throw new Exception('Unknown type of item: "' . gettype($item) . '"');
			}
		}
		unset($item);
		
		$this->_pointer = 0;
		$this->_map     = array_keys($this->_data);
		
		return $this;
	}
	
	/**
	 * Set a class of a models
	 *
	 * @param  string $modelClass
	 * @throws Exception
	 * @return Collection
	 */
	public function setModel($modelClass) {
		$reflection = new ReflectionClass($modelClass);
		if (! $reflection->implementsInterface('ZExt\Model\ModelInterface')) {
			throw new Exception('Class of a model must implements "ModelInterface"');
		}
		
		$this->_modelClass = $modelClass;
		
		if (! empty($this->_models)) {
			$currentModels = $this->_models;
			$this->_models = [];

			foreach ($currentModels as $key => $model) {
				if ($model instanceof $modelClass) {
					$this->_models[$key] = $model;
				} else {
					$model->uninitialize();
				}
			}
		}
				
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
	 * Get a name of a collection's models
	 * 
	 * @return string
	 */
	public function getName() {
		$pos = strrpos($this->_modelClass, '\\');
		
		return substr($this->_modelClass, $pos + 1);
	}
	
	/**
	 * Set a property as a primary
	 * 
	 * @param  string | array $primary
	 * @return Collection
	 */
	public function setPrimary($primaryName) {
		if (is_array($primaryName)) {
			$idProperty = self::COMPOSITE_ID_PROPERTY . $this->_collectionId;
			
			foreach ($this->_data as &$dataPart) {
				$dataHashParts = [];
				
				foreach ($primaryName as $keyPart) {
					if (! isset($dataPart[$keyPart])) {
						throw new Exception('Undefined property can\'t been used as a part of a composite primary');
					}
					
					$dataHashParts[] = $dataPart[$keyPart];
				}
				
				$dataPart[$idProperty] = $this->arrayHashFunction($dataHashParts);
			}
			unset($dataPart);
			
			$this->_primaryDefinition = $primaryName;
			
			$primaryName = $idProperty;
		} else {
			$this->_primaryDefinition = $primaryName;
		}
		
		$tempData = $this->_data;
		$this->_data = [];
		
		foreach ($tempData as &$item) {
			if (! isset($item[$primaryName])) {
				throw new Exception('Undefined property can\'t been used as a primary');
			}
			
			$this->_data[$item[$primaryName]] = &$item;
		}
		unset($item, $tempData);
		
		if (! empty($this->_models)) {
			$tempData = $this->_models;
			$this->_models = [];
			
			foreach ($tempData as $item) {
				$this->_models[$item->$primaryName] = $item;
			}
			
			unset($tempData);
		}
		
		$this->_pointer = 0;
		$this->_map     = array_keys($this->_data);
		$this->_primary = $primaryName;
		
		return $this;
	}
	
	/**
	 * Get a primary property
	 * 
	 * @return string
	 */
	public function getPrimary() {
		return $this->_primary;
	}
	
	/**
	 * Get a first item
	 * 
	 * @return Model | null
	 */
	public function getFirst() {
		if (isset($this->_map[0])) {
			return $this->getItem($this->_map[0]);
		}
	}
	
	/**
	 * Get the item
	 * 
	 * @param  int | string $key
	 * @return Model | null
	 */
	public function getItem($key) {
		if (is_array($key)) {
			$key = $this->arrayHashFunction($key);
		}
		
		if (isset($this->_models[$key])) {
			return $this->_models[$key];
		}
		
		if (isset($this->_data[$key])) {
			$modelClass = $this->getModel();
			
			$item = $modelClass::factory($this->_data[$key]);
			$item->setCollection($this);
			
			if ($this->hasDatagate()) {
				$item->setDatagate($this->getDatagate());
			}
			
			if ($this->hasLocator()) {
				$item->setLocator($this->getLocator());
			}
			
			$this->_models[$key] = $item;
			
			return $item;
		}
	}
	
	/**
	 * Set an item
	 * 
	 * @param  string | int    $key
	 * @param  array  | object $item
	 * @throws Exception
	 * @return Collection
	 */
	public function setItem($key, $item) {
		if (is_array($key)) {
			$key = $this->arrayHashFunction($key);
		}
		
		if (! isset($this->_data[$key])) {
			$this->_map[] = $key;
		}
		
		if (is_object($item)) {
			$modelClass = $this->getModel();
			if (! $item instanceof $modelClass) {
				throw new Exception('Model must be an instance of "' . $modelClass . '"');
			}
			
			$item->setCollection($this);
			
			$this->_data[$key]   = &$item->getDataLinked();
			$this->_models[$key] = $item;
		}
		elseif (is_array($item)) {
			$this->_data[$key] = $item;
		}
		else {
			throw new Exception('Unknown type of item: "' . gettype($item) . '"');
		}
		
		return $this;
	}
	
	/**
	 * Add an item to a collection
	 * 
	 * @param  array  | object $item
	 * @throws Exception
	 * @return Collection
	 */
	public function addItem($item) {
		if (is_object($item)) {
			$modelClass = $this->getModel();
			if (! $item instanceof $modelClass) {
				throw new Exception('Model must be an instance of "' . $modelClass . '"');
			}
			
			$item->setCollection($this);
			
			if ($this->_primary === null) {
				$this->_data[]   = &$item->getDataLinked();
				$this->_models[] = $item;
				
				end($this->_data);
				$this->_map[] = key($this->_data);
			} else {
				if (is_array($this->_primaryDefinition)) {
					$idParts = [];
					
					foreach ($this->_primaryDefinition as $primaryPart) {
						if (! isset($item->$primaryPart)) {
							throw new Exception('Primary part "' . $primaryPart . '" not found');
						}
						
						$idParts[] = $item->$primaryPart;
					}
					
					$id = $this->arrayHashFunction($idParts);
				} else {
					if (! isset($item->{$this->_primary})) {
						throw new Exception('Primary "' . $this->_primary . '" not found');
					}
					
					$id = $item->{$this->_primary};
				}
				
				$this->_map[]       = $id;
				$this->_data[$id]   = &$item->getDataLinked();
				$this->_models[$id] = $item;
			}
		} elseif (is_array($item)) {
			if ($this->_primary === null) {
				$this->_data[] = $item;
				
				end($this->_data);
				$this->_map[] = key($this->_data);
				
			} else {
				if (is_array($this->_primaryDefinition)) {
					$idParts = [];
					
					foreach ($this->_primaryDefinition as $primaryPart) {
						if (! isset($item[$primaryPart])) {
							throw new Exception('Primary part "' . $primaryPart . '" not found');
						}
						
						$idParts[] = $item->$primaryPart;
					}
					
					$id = $this->arrayHashFunction($idParts);
				} else {
					if (! isset($item[$this->_primary])) {
						throw new Exception('Primary "' . $this->_primary . '" not found');
					}
					
					$id = $item[$this->_primary];
				}
				
				$this->_map[]     = $id;
				$this->_data[$id] = $item;
			}
		} else {
			throw new Exception('Unknown type of item: "' . gettype($item) . '"');
		}
		
		return $this;
	}
	
	/**
	 * Add an array of items to a collection
	 * 
	 * @param  array $items
	 * @return Collection
	 */
	public function addItems(array $items) {
		foreach ($items as $item) {
			$this->addItem($item);
		}
		
		return $this;
	}
	
	/**
	 * Processing a collection's data
	 *
	 * @param  Closure $callback callback
	 * @return Collection
	 */
	public function walk(Closure $callback) {
		foreach($this->_data as &$data) {
			$data = $callback($data);
		}
		unset($data);
		
		return $this;
	}
	
	/**
	 * Cast a property to a type
	 * 
	 * @param  string       $property
	 * @param  int | string $type
	 * @param  bool         $useFactory
	 * @return Collection
	 */
	public function cast($property, $type, $useFactory = false) {
		foreach($this->_data as &$data) {
			if (! array_key_exists($property, $data)) {
				continue;
			}
			
			if (is_string($type)) {
				if ($useFactory) {
					$data[$property] = $type::factory($data[$property]);
				} else {
					$data[$property] = new $type($data[$property]);
				}
				
				continue;
			} elseif ($type === self::TYPE_MODEL) {
				$modelClass = $this->getModel();
			}
			
			switch ($type) {
				case self::TYPE_BOOL:
					$data[$property] = (bool) $data[$property];
					break;
				
				case self::TYPE_INT:
					$data[$property] = (int) $data[$property];
					break;
				
				case self::TYPE_FLOAT:
					$data[$property] = (float) $data[$property];
					break;
				
				case self::TYPE_DOUBLE:
					$data[$property] = (double) $data[$property];
					break;
				
				case self::TYPE_STRING:
					$data[$property] = (string) $data[$property];
					break;
				
				case self::TYPE_ARRAY:
					$data[$property] = (array) $data[$property];
					break;
				
				case self::TYPE_OBJECT:
					$data[$property] = (object) $data[$property];
					break;
				
				case self::TYPE_MODEL:
					$data[$property] = $modelClass::factory($data[$property]);
					break;
				
				case self::TYPE_COLLECTION:
					$data[$property] = static::factory($data[$property]);
					break;
			}
		}
		
		return $this;
	}
	
	/**
	 * Sort the items order
	 * Affects iteration and some methods, like "toArray()", "find()" etc.
	 * 
	 * Usage:
	 * sort('property1', 'property2', 'PropertyN');
	 * 
	 * With a direction:
	 * sort('property ASC')  - ascend (default)
	 * sort('property DESC') - descend
	 * 
	 * With definition as an array:
	 * sort(['property1', 'property2', 'propertyN'])
	 *
	 * @param  string | array $definition
	 * @return Collection
	 * @throws Exception
	 */
	public function sort($definition = null) {
		if (func_num_args() > 1) {
			$definition = func_get_args();
		}
		else if ($definition === null) {
			$definition = $this->getPrimary();
			
			if ($definition === null) {
				throw new Exception('Unable to sort by primary property due to primary is not set');
			}
		}
		
		$this->_sortDefinition = (array) $definition;
		
		$this->buildSortedMap($this->_sortDefinition);
		
		return $this;
	}
	
	/**
	 * Build iteration map by data columns
	 * 
	 * @param array $definition
	 */
	protected function buildSortedMap(array $definition) {
		$sortArgs = [];
		
		foreach ($definition as $definitionPart) {
			list($property, $direction) = $this->parseSortDefinition($definitionPart);
			
			$sortArgs[] = $this->getDataColumn($property);
			$sortArgs[] = $direction;
		}
		
		$this->_map = array_keys($this->_data);
		$sortArgs[] = &$this->_map;
		
		call_user_func_array('array_multisort', $sortArgs);
	}
	
	/**
	 * Parse the sort definition
	 * 
	 * @param  string $definition
	 * @return array  [property, direction]
	 * @throws Exception
	 */
	protected function parseSortDefinition($definition) {
		$definition = trim($definition);
		$spacePos   = strrpos($definition, ' ');
		
		if ($spacePos === false) {
			return [$definition, SORT_ASC];
		}
		
		$property  = substr($definition, 0, $spacePos);
		$direction = substr($definition, $spacePos);
		$direction = strtolower(ltrim($direction));

		if ($direction === self::SORT_ASC) {
			return [$property, SORT_ASC];
		}
		
		if ($direction === self::SORT_DESC) {
			return [$property, SORT_DESC];
		}
		
		throw new Exception('Unknown sort direction: "' . $direction . '"');
	}
	
	/**
	 * Get the data column
	 * 
	 * @param  string $column
	 * @return array
	 */
	protected function getDataColumn($column) {
		if (function_exists('array_column')) {
			return array_column($this->_data, $column);
		}
		
		$columnData = [];
		
		foreach ($this->_data as $row) {
			$columnData[] = $row[$column];
		}
		
		return $columnData;
	}
	
	/**
	 * Set mode of a collection's iteration
	 * 
	 * @param  string | int $mode
	 * @return Collection
	 */
	public function setIterationMode($mode) {
		switch ($mode) {
			case self::ITERATION_STRAIGHT:
				$this->_map = array_keys($this->_data);
				break;
			
			case self::ITERATION_REVERSE:
				$this->_map = array_reverse(array_keys($this->_data));
				break;
			
			case self::ITERATION_EVEN:
				$this->_map = [];
				
				foreach (array_keys($this->_data) as $key => $value) {
					if ($key % 2 === 0) {
						$this->_map[] = $value;
					}
				}
				break;
				
			case self::ITERATION_ODD:
				$this->_map = [];
				
				foreach (array_keys($this->_data) as $key => $value) {
					if ($key % 2 !== 0) {
						$this->_map[] = $value;
					}
				}
				break;
		}
		
		return $this;
	}
	
	/**
	 * Get a collection of properties-models
	 * 
	 * @param  string $property
	 * @param  string $primary
	 * @return Collection
	 * @throws Exception
	 */
	public function getCollection($property, $model = null, $primary = null) {
		$list = $this->getList($property);
		
		// Integrity checking
		foreach ($list as $key => $item) {
			if ($item === null) {
				unset($list[$key]);
				continue;
			}
			
			if (! is_object($item)) {
				throw new Exception('Unable to create a collection of non objects');
			}
			
			if (! $item instanceof ModelInterface) {
				throw new Exception('Object must implements "ModelInterface" to be in collection');
			}
		}
		
		// Model's class name resolve
		if ($model === null && isset($item) && $item->hasCollection()) {
			$model = $item->getCollection()->getModel();
		}
		
		if ($model === null && isset($item)) {
			$currentClass   = get_class($item);
			$extensionStack = array($currentClass);
			
			while ($currentClass = get_parent_class($currentClass)) {
				$extensionStack[] = $currentClass;
			}
			
			$primaryIndex = array_search(self::PRIMARY_MODEL, $extensionStack);
			
			if ($primaryIndex === 0 || $primaryIndex === false) {
				$model = self::PRIMARY_MODEL;
			} else {
				$model = $extensionStack[$primaryIndex - 1];
			}
		}
		
		// Primary resolve
		if ($primary === false) {
			$primary = null;
		} else if ($primary === null && isset($item) && $item->hasCollection()) {
			$primary = $item->getCollection()->getPrimary();
		}
		
		$collection = new self($list, $model, $primary);
		
		if ($this->hasLocator()) {
			$collection->setLocator($this->getLocator());
		}
		
		return $collection;
	}
	
	/**
	 * Get list of values by a single property considering an order
	 * 
	 * @param  string $propertyValues property of values
	 * @param  string $propertyKeys property of array's keys if need
	 * @param  bool   $uniqueValues
	 * @return array
	 */
	public function getList($propertyValues, $propertyKeys = null, $uniqueValues = false) {
		$this->initialize($propertyValues);
		
		$list = [];		
		
		if ($propertyKeys === true) {
			$propertyKeys = $this->getPrimary();
		}
		
		if ($propertyKeys === null) {
			foreach($this->_map as $key) {
				if (isset($this->_data[$key][$propertyValues])) {
					$list[] = $this->_data[$key][$propertyValues];
				}
			}
		} else {
			foreach ($this->_map as $key) {
				if (isset($this->_data[$key][$propertyKeys], $this->_data[$key][$propertyValues])) {
					$list[$this->_data[$key][$propertyKeys]] = $this->_data[$key][$propertyValues];
				}
			}
		}
		
		if ($uniqueValues === true) {
			return array_unique($list);
		}
		
		return $list;
	}
	
	/**
	 * Set the list of a values to the data
	 * 
	 * @param  string $property
	 * @param  mixed  $data
	 * @param  mixed  $primary
	 * @param  bool   $overwrite
	 * @return Collection
	 */
	public function setList($property, $data, $primary = null, $overwrite = true) {
		if ($primary !== true && (is_array($data) || $data instanceof Collection)) {
			if ($primary === null) {
				$primary = $this->getPrimary();
				
				if ($primary === null) {
					throw new Exception('Unable to determine a primary property');
				}
			}
			
			if ($overwrite) {
				foreach ($this->_data as &$item) {
					if (isset($data[$item[$primary]])) {
						$item[$property] = $data[$item[$primary]];
					}
				}
			} else {
				foreach ($this->_data as &$item) {
					if (isset($data[$item[$primary]]) && ! isset($item[$property])) {
						$item[$property] = $data[$item[$primary]];
					}
				}
			}
		} else {
			if ($overwrite) {
				foreach ($this->_data as &$item) {
					$item[$property] = $data;
				}
			} else {
				foreach ($this->_data as &$item) {
					if (! isset($item[$property])) {
						$item[$property] = $data;
					}
				}
			}
		}
		
		return $this;
	}

	/**
	 * Find an items with a property equals a value (or an array of a values) considering an order
	 * 
	 * @param  string $property
	 * @param  mixed  $value
	 * @param  int | null $limit
	 * @return Collection
	 */
	public function find($property, $value = true, $limit = null) {
		if ($limit === null) {
			$limit = count($this->_map);
		}
		
		$data    = $this->createClone();
		$isArray = is_array($value);
		
		foreach ($this->_map as $key) {
			$item = $this->_data[$key];
			
			if ($limit < 1) break;
			
			if (isset($item[$property]) && ($isArray ? in_array($item[$property], $value) : $item[$property] == $value)) {
				if (isset($this->_models[$key])) {
					$data->setItem($key, $this->_models[$key]);
				} else {
					$data->setItem($key, $item);
				}
				
				--$limit;
			}
			
		}
		
		return $data;
	}
	
	/**
	 * Get chunk of the collection
	 * 
	 * @param  int $size
	 * @param  int $offset
	 * @return Collection
	 */
	public function chunk($size, $offset = 0) {
		$data      = $this->createClone();
		$availSize = count($this->_map) - $offset;
		
		if ($size > $availSize) {
			$size = $availSize;
		}
		
		$last = $size + $offset;
		
		for ($i = $offset; $i < $last; ++$i) {
			$key = $this->_map[$i];
			
			if (isset($this->_models[$key])) {
				$data->setItem($key, $this->_models[$key]);
			} else {
				$data->setItem($key, $this->_data[$key]);
			}
		}
		
		return $data;
	}
	
	/**
	 * Fetch data partly as a collection
	 * 
	 * @param  array $propertiesList
	 * @return Collection
	 */
	public function fetch(array $propertiesList) {
		$data = $this->createClone();
		
		foreach ($this->_map as $key) {
			$item = $this->_data[$key];
			$itemFetched = [];
			
			foreach ($propertiesList as $property) {
				if (isset($item[$property])) {
					$itemFetched[$property] = $item[$property];
				}
			}
			
			if (! empty($itemFetched)) {
				$data->addItem($itemFetched);
			}
		}
		
		return $data;
	}
	
	/**
	 * Get amount of the properties
	 * 
	 * @param  string $property
	 * @return int
	 */
	public function sum($property) {
		$sum = 0;
		
		foreach ($this->_map as $key) {
			$item = $this->_data[$key];
			
			if (isset($item[$property])) {
				$sum += $item[$property];
			}
		}
		
		return $sum;
	}
	
	/**
	 * Get a list of objects' properties
	 * 
	 * @return array 
	 */
	public function getPropertiesNames() {
		$propertiesList = [];
		
		foreach($this->_data as $item) {
			$propertiesList += array_keys($item);
		}
		
		return $propertiesList;
	}
	
	/**
	 * Merge items of source collection into items of this collection
	 * 
	 * @param  Collection $collection
	 * @param  string $primarySource
	 * @param  string $primaryDest
	 * @throws Exception
	 * @return Collection
	 */
	public function join(Collection $collection, $onProperty = null, $sourceProperty = null) {
		if ($collection->isEmpty()) {
			return $this;
		}
		
		$sourcePrimary = $collection->getPrimary();
		
		if ($onProperty === null) {
			if ($sourcePrimary === null) {
				throw new Exception('Unable to determine a property for join');
			} else {
				$onProperty = $sourcePrimary;
			}
		}
		
		if ($sourceProperty === null) {
			$sourceProperty = $onProperty;
		}

		if ($sourceProperty !== $sourcePrimary) {
			$collection = clone $collection;
			$collection->setPrimary($sourceProperty);
		}
		
		$source = &$collection->getDataLinked();

		foreach ($this->_data as &$item) {
			if (isset($item[$onProperty], $source[$item[$onProperty]])) {
				$item += $source[$item[$onProperty]];
			}
		}
		unset($item, $source);
		
		return $this;
	}
	
	/**
	 * Merge a collection into this collection
	 * 
	 * @param  Collection $collection
	 * @return Collection
	 */
	public function merge(Collection $collection) {
		if ($collection->isEmpty()) {
			return $this;
		}
		
		$primary = $this->getPrimary();
		
		if ($primary !== null) {
			$sourcePrimary = $collection->getPrimary();
		
			if ($primary !== $sourcePrimary) {
				$collection = clone $collection;
				$collection->setPrimary($primary);
			}
		}
		
		$data   = &$collection->getDataLinked();
		$models = $collection->getInstancedModels();
		
		if ($primary === null) {
			$this->_data = array_merge($this->_data, $data);
			
			if (! empty($models)) {
				$this->_models = array_merge($this->_models , $models);
			}
		} else {
			$this->_data = $data + $this->_data;
			
			if (! empty($models)) {
				$this->_models = $models + $this->_models;
			}
		}
		
		$this->_pointer = 0;
		$this->_map     = array_keys($this->_data);
		
		return $this;
	}
	
	/**
	 * Truncate a collection's data
	 * 
	 * @return Collection
	 */
	public function truncate() {
		$this->_map     = [];
		$this->_data    = [];
		$this->_models  = [];
		$this->_pointer = 0;
		
		return $this;
	}

	/**
	 * Get a models which were instanced
	 * 
	 * @return Model[]
	 */
	public function getInstancedModels() {
		return $this->_models;
	}
	
	/**
	 * Get collection as an array considering an order
	 * 
	 * @param  bool $recursively Models to arrays also
	 * @return array
	 */
	public function toArray($recursively = false) {
		$array = [];
		
		if ($recursively === true) {
			foreach($this->_map as $key) {
				$array[$key] = $this->getItem($key)->toArray();
			}
		} else {
			foreach($this->_map as $key) {
				$array[$key] = $this->getItem($key);
			}
		}

		return $array;
	}
	
	/**
	 * Get collection as a json encoded string considering an order
	 * 
	 * @return string 
	 */
	public function toJson() {
		return json_encode(array_values($this->toArray(true)));
	}
	
	/**
	 * Item exists check
	 * 
	 * @param  string | int $key
	 * @return bool
	 */
	public function issetItem($key) {
		if (is_array($key)) {
			$key = $this->arrayHashFunction($key);
		}
		
		return isset($this->_data[$key]);
	}
	
	/**
	 * Unset an item
	 * 
	 * @param  string | int $key
	 * @return Collection
	 */
	public function unsetItem($key) {
		if (is_array($key)) {
			$key = $this->arrayHashFunction($key);
		}
		
		if (! isset($this->_data[$key])) {
			return;
		}
		
		unset($this->_data[$key], $this->_models[$key]);
		$this->_map = array_keys($this->_data);
		
		return $this;
	}
	
	/**
	 * Unset a property in each item
	 * 
	 * @param  string $property
	 * @return Collection
	 */
	public function unsetProperty($property) {
		foreach ($this->_data as &$item) {
			unset($item[$property]);
		}
		unset($item);
		
		return $this;
	}
	
	/**
	 * Create the same empty collection
	 * 
	 * @return Collection
	 */
	public function createClone($data = []) {
		$collection = static::factory($data, $this->getModel(), $this->getPrimary());
		
		if ($this->hasDatagate()) {
			$collection->setDatagate($this->getDatagate());
		}
		
		if ($this->hasLocator()) {
			$collection->setLocator($this->getLocator());
		}
		
		return $collection;
	}
	
	/**
	 * Inititalize data
	 * 
	 * @param  string | array $property
	 * @return Collection
	 */
	public function initialize($property = null) {
		if (! isset($this->_map[0])) {
			return $this;
		}
		
		$key  = $this->_map[0];
		$item = &$this->_data[$key];
		
		if (isset($item[$property])) {
			return $this;
		}
		
		$this->getItem($key)->initialize($property);
		
		return $this;
	}
	
	/**
	 * Get a hash of an array
	 * 
	 * @param  array $in
	 * @return string
	 */
	protected function arrayHashFunction(array $in) {
		return json_encode($in);
	}
	
	/**
	 * Count number of items
	 * 
	 * @param  int | array count by property(ies)
	 * @return int
	 */
	public function count($property = null) {
		if ($property === null) {
			return count($this->_map);
		}
		
		if (is_array($property)) {
			$reduceFunction = function(&$result, &$item) use($property) {
				foreach ($property as $part) {
					if (! isset($item[$part])) {
						return;
					}
				}
				
				return $result + 1;
			};
		} else {
			$reduceFunction = function(&$result, &$item) use($property) {
				if (isset($item[$property])) {
					return $result + 1;
				};
			};
		}
		
		return (int) array_reduce($this->_data, $reduceFunction, 0);
	}
	
	/**
	 * Get list of values
	 * 
	 * @param  string $name
	 * @return array
	 */
	public function __get($name) {
		return $this->getList($name);
	}
	
	/**
	 * Set list of values
	 * 
	 * @param string $name
	 * @param array  $value
	 */
	public function __set($name, $value) {
		$this->setList($name, $value);
	}
	
	/**
	 * Unset a property in each item
	 * 
	 * @param type $name
	 */
	public function __unset($name) {
		$this->unsetProperty($name);
	}
	
	// ArrayAccess interface:

	public function offsetGet($offset) {
		return $this->getItem($offset);
	}

	public function offsetSet($offset, $value){
		if ($offset === null) {
			$this->addItem($value);
		} else {
			$this->setItem($offset, $value);
		}
	}
	
	public function offsetExists($offset) {
		return $this->issetItem($offset);
	}

	public function offsetUnset($offset) {
		$this->unsetItem($offset);
	}
	
	// SeekableIterator interface:

	public function seek($pointer) {
		$this->_pointer = (int) $pointer;
	}

	public function current() {
		if (isset($this->_map[$this->_pointer])) {
			return $this->getItem($this->_map[$this->_pointer]);
		}
	}

	public function next() {
		++ $this->_pointer;
	}

	public function key() {
		if ($this->_primary === null) {
			return $this->_pointer;
		} else {
			return $this->_data[$this->_map[$this->_pointer]][$this->_primary];
		}
	}

	public function valid() {
		return isset($this->_map[$this->_pointer]);
	}

	public function rewind() {
		$this->_pointer = 0;
	}
	
	public function __sleep() {
		return array_merge(
			parent::__sleep(),
			[
				'_modelClass', 
				'_primary',
				'_primaryDefinition',
				'_sortDefinition'
			]
		);
	}
	
	public function __wakeup() {
		parent::__wakeup();
		
		$this->_pointer      = 0;
		$this->_collectionId = self::$_collectionIdCounter ++;
		
		if ($this->_sortDefinition === null) {
			$this->_map = array_keys($this->_data);
		} else {
			$this->buildSortedMap($this->_sortDefinition);
		}
	}
	
	public function __clone() {
		$this->_collectionId = self::$_collectionIdCounter ++;
	}
	
}