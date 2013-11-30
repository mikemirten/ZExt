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

namespace ZExt\Datagate;

use ZExt\Di\LocatorAwareInterface,
    ZExt\Di\LocatorAwareTrait,
	ZExt\Di\LocatorInterface;

use ZExt\Cache\CacheAwareInterface,
    ZExt\Cache\CacheAwareTrait;

use ZExt\Components\OptionsTrait;
use ZExt\Model\Iterator;

use ReflectionObject, Traversable, ArrayIterator;

use ZExt\Datagate\Exceptions\InvalidResultType;

/**
 * Interface of a gateway to a data and a data to a model mapper
 * 
 * @category   ZExt
 * @package    Datagate
 * @subpackage Datagate
 * @author     Mike.Mirten
 * @version    3.0 
 */
abstract class DatagateAbstract

	implements DatagateInterface,
	           LocatorAwareInterface,
	           CacheAwareInterface {
	
	use OptionsTrait;
	use LocatorAwareTrait;
	use CacheAwareTrait;
	
	const DATAGATE_POSTFIX   = 'Datagate';
	const MODEL_POSTFIX      = 'Model';
	const MODEL_DEFAULT      = 'ZExt\Model\Model';
	const COLLECTION_DEFAULT = 'ZExt\Model\Collection';
	
	/**
	 * Introspective data of the datagae
	 *
	 * @var stdClass
	 */
	private $_introspectiveData;
	
	/**
	 * Class of the models
	 * 
	 * Can be overrided by a user
	 *
	 * @var string
	 */
	private $model;
	
	/**
	 * Class of the collections
	 * 
	 * Can be overrided by a user
	 *
	 * @var string 
	 */
	private $collection = self::COLLECTION_DEFAULT;
	
	/**
	 * Name of the table or collection
	 * 
	 * Can be overrided by a user
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 * Name of the primary identity
	 * 
	 * Name of a column for a SQL database,
	 * or a property for a document oriented database
	 * 
	 * Can be array for a composite primary identity
	 * 
	 * Can be overrided by a user
	 *
	 * @var string | array 
	 */
	protected $primary;
	
	/**
	 * Type of a result of an item
	 * 
	 * Can be overrided by a user
	 * Should be used the self::RESULT_* & self::RESULTSET_* constants
	 *
	 * @var int
	 */
	protected $result;
	
	/**
	 * Constructor
	 * 
	 * @param array | Traversable | LocatorInterface $options
	 */
	public function __construct($options = null) {
		if ($options !== null) {
			if (is_array($options) || $options instanceof Traversable) {
				$this->setOptions($options);
			}
			else if ($options instanceof LocatorInterface) {
				$this->setLocator($options);
			}
		}
		
		$this->init();
	}
	
	/**
	 * User's initialization procedure, calls at the end of construction
	 */
	protected function init() {}
	
	/**
	 * Create the result
	 * 
	 * @param  array $data
	 * @param  int   $type
	 * @return mixed
	 */
	protected function createResult(array &$data, $type = null) {
		if ($type === null) {
			$type = $this->getResultType();
		}
		
		if ($type & self::RESULT_ARRAY) {
			return $data;
		}
		
		if ($type & self::RESULT_OBJECT) {
			return (object) $data;
		}
		
		if ($type & self::RESULT_MODEL) {
			return $this->create($data);
		}
		
		throw new InvalidResultType('Nothing to do with the type #' . $type);
	}
	
	/**
	 * Create the resultset
	 * 
	 * Each element of the source (array | Iterator) must be an array
	 * 
	 * @param  array | Traversable $data
	 * @param  int                 $type
	 * @return mixed
	 */
	protected function createResultset(&$data, $type = null) {
		if ($type === null) {
			$type = $this->getResultType();
		}
		
		// Resultset = array
		if ($type & self::RESULTSET_ARRAY) {
			if ($data instanceof Traversable) {
				$data = iterator_to_array($data);
			}
			
			if ($type & self::RESULT_ARRAY) {
				return $data;
			}
			
			if ($type & self::RESULT_OBJECT) {
				return array_map(function(&$in) {
					return (object) $in;
				}, $data);
			}
			
			if ($type & self::RESULT_MODEL) {
				return array_map([$this, 'create'], $data);
			}
		}
		
		// Resultset = collection
		if ($type & self::RESULTSET_COLLECTION) {
			if ($data instanceof Traversable) {
				$data = iterator_to_array($data);
			}
			
			return $this->createCollection($data);
		}
		
		// Resultset = iterator
		if ($type & self::RESULTSET_ITERATOR) {
			if (is_array($data)) {
				$data = new ArrayIterator($data);
			}
			
			if ($type & self::RESULT_ARRAY) {
				return $data;
			}
			
			if ($type & self::RESULT_OBJECT) {
				foreach ($data as &$item) {
					$item = (object) $item;
				} unset ($item);
				
				return $data;
			}
			
			if ($type & self::RESULT_MODEL) {
				return $this->createIterator($data);
			}
		}
		
		throw new InvalidResultType('Nothing to do with the type #' . $type);
	}
	
	/**
	 * Create the iterator
	 * 
	 * @param  Traversable $sourceIterator
	 * @return ModelIterator
	 */
	protected function createIterator(Traversable $sourceIterator) {
		$modelClass = $this->getModelClass();
		
		$iterator = new Iterator($sourceIterator, $modelClass);
		$iterator->setParentDatagate($this);
		
		if ($this->hasLocator()) {
			$iterator->setLocator($this->getLocator());
		}
		
		return $iterator;
	}
	
	/**
	 * Create a new model, empty or with the supplied data
	 * 
	 * @param  array $data initial data for the the model
	 * @return \ZExt\Model\Model
	 */
	public function create(array &$data = null) {
		$modelClass = $this->getModelClass();
		
		$model = $modelClass::factory($data);
		$model->setParentDatagate($this);
		
		if ($this->hasLocator()) {
			$model->setLocator($this->getLocator());
		}
		
		if (empty($data)) {
			$model->forceInsert();
		}
		
		return $model;
	}
	
	/**
	 * Create a new collection, empty or with the supplied data
	 * 
	 * @param  array $data initial data for the collection
	 * @return \ZExt\Model\Collection
	 */
	public function createCollection(array &$data = null) {
		$collectionClass = $this->getCollectionClass();
		$modelClass      = $this->getModelClass();
		$primary         = $this->getPrimaryName();
		
		// Primary ID data handling
		if (! empty($data) || $primary !== null) {
			$validation = true;
			
			array_walk($data, function(&$item) use($primary, &$validation) {
				if (! isset($item[$primary])) {
					$validation = false;
				}
			});
			
			if ($validation) {
				array_walk($data, function(&$item) use($primary) {
					if (is_object($item[$primary])) {
						$item[$primary] = (string) $item[$primary];
					}
				});
			} else {
				$primary = null;
			}
		}
		
		// Creating and supplying of the dependencies
		$collection = $collectionClass::factory($data, $modelClass, $primary);
		$collection->setParentDatagate($this);
		
		if ($this->hasLocator()) {
			$collection->setLocator($this->getLocator());
		}
		
		return $collection;
	}
	
	/**
	 * Set the model's class
	 * 
	 * @param string $name
	 */
	public function setModelClass($class) {
		$this->model = (string) $class;
	}
	
	/**
	 * Get model's class
	 * 
	 * @return string
	 */
	public function getModelClass() {
		if ($this->model === null) {
			$introspection = $this->getIntrospectiveData();
			
			$modelBase = $introspection->name . self::MODEL_POSTFIX;
			$modelPath = $introspection->dirname . DIRECTORY_SEPARATOR .
			             $modelBase . '.' . $introspection->extension;

			if (is_file($modelPath)) {
				$this->model = $introspection->namespace . '\\' . $modelBase;
			} else {
				$this->model = self::MODEL_DEFAULT;
			}
		}

		return $this->model;
	}
	
	/**
	 * Set the collection's class
	 * 
	 * @param string $name
	 */
	public function setCollectionClass($class) {
		$this->collection = (string) $class;
	}
	
	/**
	 * Get collection's class
	 * 
	 * @return string
	 */
	public function getCollectionClass() {
		return $this->collection;
	}
	
	/**
	 * Get the name of the table or collection
	 * 
	 * @return string
	 */
	protected function getTableName() {
		if ($this->name === null) {
			$this->name = $this->getIntrospectiveData()->name;
		}
		
		return $this->name;
	}
	
	/**
	 * Get the name of the primary identity
	 * 
	 * @return string | array | null
	 */
	protected function getPrimaryName() {
		return $this->primary;
	}
	
	/**
	 * Set the type of an item of a data
	 * See the RESULT_* constants of the DatagateInterface
	 * 
	 * @param int $type
	 */
	public function setResultType($type) {
		$this->result = (int) $type;
	}
	
	/**
	 * Get the type of an item of a data
	 * 
	 * @return int $type
	 */
	public function getResultType() {
		if ($this->result === null) {
			$this->result = self::RESULTSET_COLLECTION + self::RESULT_MODEL;
		}
		
		return $this->result;
	}
	
	/**
	 * Get the introspective data
	 * 
	 * @return object
	 */
	private function getIntrospectiveData() {
		if ($this->_introspectiveData === null) {
			$reflection = new ReflectionObject($this);

			$data = (object) pathinfo($reflection->getFileName());
			$data->namespace = $reflection->getNamespaceName();
			$data->class     = $reflection->getShortName();
			
			$postfixPos = strrpos($data->class, self::DATAGATE_POSTFIX);
			$data->name = substr($data->class, 0, $postfixPos);
			
			$this->_introspectiveData = $data;
		}
		
		return $this->_introspectiveData;
	}
	
}