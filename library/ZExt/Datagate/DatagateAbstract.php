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

use ZExt\Model\Iterator,
    ZExt\Model\Collection,
    ZExt\Model\Model;

use ReflectionObject, Traversable, ArrayIterator;

/**
 * Interface of a gateway to a data and a data to a model mapper
 * 
 * @category   ZExt
 * @package    Datagate
 * @subpackage Datagate
 * @author     Mike.Mirten
 * @version    3.2
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
	 * Database adapter name
	 * 
	 * Can be overrided by an user
	 *
	 * @var  string
	 */
	protected $adapter;
	
	/**
	 * Class of the models
	 * 
	 * Can be overrided by an user
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Class of the collections
	 * 
	 * Can be overrided by an user
	 *
	 * @var string 
	 */
	protected $collection = self::COLLECTION_DEFAULT;

	/**
	 * Name of the table or collection
	 * 
	 * Can be overrided by an user
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
	 * Can be overrided by an user
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

		throw new Exceptions\InvalidResultType('Nothing to do with the type #' . $type);
	}

	/**
	 * Create the resultset
	 * 
	 * Each element of the source (array | Iterator) must be an array or object
	 * 
	 * @param  array | Traversable $data
	 * @param  int                 $type
	 * @return mixed
	 */
	protected function createResultset(&$data, $type = null, $primaryId = null) {
		if ($type === null) {
			$type = $this->getResultType();
		}

		// Resultset = array
		if ($type & self::RESULTSET_ARRAY) {
			if ($data instanceof Traversable) {
				$data = iterator_to_array($data);
			}

			if ($type & (self::RESULT_ARRAY | self::RESULT_OBJECT)) {
				return $data;
			}

			if ($type & self::RESULT_MODEL) {
				return array_map([$this, 'create'], $data);
			}
		}

		// Resultset = collection
		if ($type & self::RESULTSET_COLLECTION) {
			if (! ($type & self::RESULT_MODEL)) {
				throw new Exceptions\InvalidResultType('Colection resultset works only with the models type');
			}
			
			if ($data instanceof Traversable) {
				$data = iterator_to_array($data);
			}

			return $this->createCollection($data, $primaryId);
		}

		// Resultset = iterator
		if ($type & self::RESULTSET_ITERATOR) {
			if (is_array($data)) {
				$data = new ArrayIterator($data);
			}

			if ($type & (self::RESULT_ARRAY | self::RESULT_OBJECT)) {
				return $data;
			}

			if ($type & self::RESULT_MODEL) {
				return $this->createIterator($data);
			}
		}

		throw new Exceptions\InvalidResultType('Nothing to do with the type #' . $type);
	}

	/**
	 * Create the iterator
	 * 
	 * @param  Traversable $sourceIterator
	 * @return ModelIterator
	 */
	protected function createIterator(Traversable $sourceIterator) {
		$iterator = new Iterator($sourceIterator, $this->getModelClass());
		$iterator->setDatagate($this);

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
		$model->setDatagate($this);

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
	 * @param  array  $data      Initial data for the collection
	 * @param  string $primaryId Specify the primary ID
	 * @return \ZExt\Model\Collection
	 */
	public function createCollection(array &$data = null, $primaryId = null) {
		$collectionClass = $this->getCollectionClass();
		$modelClass      = $this->getModelClass();
		
		if ($primaryId === null) {
			$primaryId = $this->getPrimaryName();
		}

		// Primary ID data handling
		if (! empty($data) || $primaryId !== null) {
			$validation = true;

			array_walk($data, function(&$item) use($primaryId, &$validation) {
				if (! isset($item[$primaryId])) {
					$validation = false;
				}
			});

			if ($validation) {
				array_walk($data, function(&$item) use($primaryId) {
					if (is_object($item[$primaryId])) {
						$item[$primaryId] = (string) $item[$primaryId];
					}
				});
			} else {
				$primaryId = null;
			}
		}

		// Creating and supplying of the dependencies
		$collection = $collectionClass::factory($data, $modelClass, $primaryId);
		$collection->setDatagate($this);

		if ($this->hasLocator()) {
			$collection->setLocator($this->getLocator());
		}

		return $collection;
	}

	/**
	 * Save the model or the collection of the models
	 * 
	 * @param  DatagateAwareInterface $model
	 * @param  array                  $options
	 * @return bool True if succeeded
	 * @throws Exceptions\OperationError
	 */
	public function save(DatagateAwareInterface $model, array $options = []) {
		if ($model->isEmpty()) {
			throw new Exceptions\OperationError('Model has no data');
		}
		
		if ($model instanceof Model) {
			return $this->saveModel($model, $options);
		}
		
		
		if ($model instanceof Collection) {
			return $this->saveCollection($model, $options);
		}
		
		throw new Exceptions\OperationError('Unknown instance of the "DatagateAwareInterface" was given: "' . get_class($model) . '"');
	}
	
	/**
	 * Save the model
	 * 
	 * @param  Model $model
	 * @return bool
	 * @throws Exceptions\OperationError
	 */
	protected function saveModel(Model $model) {
		throw new Exceptions\OperationError('Saving of model doesn\'t implemented in the datagate');
	}
	
	/**
	 * Save the collection
	 * 
	 * @param  Collection $collection
	 * @return bool
	 * @throws Exceptions\OperationError
	 */
	protected function saveCollection(Collection $collection) {
		throw new Exceptions\OperationError('Saving of collection doesn\'t implemented in the datagate');
	}
	
	/**
	 * Remove the record or the many of records by the model or the collection of the models
	 * 
	 * @param  DatagateAwareInterface $model
	 * @param  array                  $options
	 * @return bool True if succeeded
	 * @throws Exceptions\OperationError
	 */
	public function remove(DatagateAwareInterface $model, array $options = []) {
		if ($model->isEmpty()) {
			throw new Exceptions\OperationError('Model has no data');
		}
		
		if ($model instanceof Model) {
			return $this->removeModel($model, $options);
		}
		
		
		if ($model instanceof Collection) {
			return $this->removeCollection($model, $options);
		}
		
		throw new Exceptions\OperationError('Unknown instance of the "DatagateAwareInterface" was given: "' . get_class($model) . '"');
	}
	
	/**
	 * Remove the model
	 * 
	 * @param  Model $model
	 * @return bool
	 * @throws Exceptions\OperationError
	 */
	protected function removeModel(Model $model) {
		throw new Exceptions\OperationError('Removing of model doesn\'t implemented in the datagate');
	}
	
	/**
	 * Remove the collection
	 * 
	 * @param  Collection $collection
	 * @return bool
	 * @throws Exceptions\OperationError
	 */
	protected function removeCollection(Collection $collection) {
		throw new Exceptions\OperationError('Removing of collection doesn\'t implemented in the datagate');
	}
	
	/**
	 * Set the model's class
	 * 
	 * @param string $class
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

			$this->model = (is_file($modelPath))
				? $introspection->namespace . '\\' . $modelBase
				: self::MODEL_DEFAULT;
		}

		return $this->model;
	}

	/**
	 * Set the collection's class
	 * 
	 * @param string $class
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
	 * Set the name of the table or collection
	 * 
	 * @param string $name
	 */
	public function setTableName($name) {
		$this->name = (string) $name;
	}

	/**
	 * Get the name of the table or collection
	 * 
	 * @return string
	 */
	public function getTableName() {
		if ($this->name === null) {
			$this->name = lcfirst($this->getIntrospectiveData()->name);
		}

		return $this->name;
	}
	
	/**
	 * Set the name of the primary identity
	 * 
	 * @param string | array $primary
	 */
	public function setPrimaryName($primary) {
		if (is_string($primary) || is_array($primary)) {
			$this->primary = $primary;
			return;
		}
		
		throw new Exceptions\InvalidOption('Name of primary identity must be a string or an array, "' . gettype($primary) . '" was given');
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
			$this->result = self::RESULTSET_COLLECTION | self::RESULT_MODEL;
		}

		return $this->result;
	}

	/**
	 * Get the introspective data
	 * 
	 * @return object
	 */
	protected function getIntrospectiveData() {
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
	
	/**
	 * Set the name of the adapter service in a services locator
	 * 
	 * @param string $name
	 */
	public function setAdapterName($name) {
		$this->adapter = (string) $name;
	}
	
	/**
	 * Get the name of the adapter service in a services locator
	 * 
	 * @return string
	 */
	public function getAdapterName() {
		return $this->adapter;
	}

}