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

use ZExt\Datagate\Exceptions\NoAdapter;
use ZExt\Datagate\Exceptions\OperationError;

use ZExt\NoSql\Adapter\MongoAdapter;

use ZExt\Paginator\Adapter\MongoCursorAdapter as PaginatorAdapter,
	ZExt\Paginator\Paginator,
    ZExt\Model\Iterator;

use ZExt\Model\ModelInterface,
	ZExt\Model\Collection,
	ZExt\Model\Model;

use MongoId;

/**
 * MongoDB collection datagate
 * 
 * @category   ZExt
 * @package    Datagate
 * @subpackage MongoDB
 * @version    2.0
 */
class MongoCollection extends DatagateAbstract {
	
	const DEFAULT_ADAPTER = 'mongodbAdapter';
	const DEFAULT_PRIMARY = '_id';
	
	/**
	 * Database adapter name
	 * 
	 * Can be overrided by an user
	 *
	 * @var string
	 */
	protected $adapter = self::DEFAULT_ADAPTER;
	
	/**
	 * MongoDB connection adapter
	 *
	 * @var MongoAdapter
	 */
	private $_adapter;
	
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
	protected $primary = self::DEFAULT_PRIMARY;
	
	/**
	 * Constructor
	 * 
	 * @param MongoAdapter         $adapter Adapter instance or a services locator or an options
	 * @param array | \Traversable $options
	 */
	public function __construct($adapter = null, $options = null) {
		if ($adapter instanceof MongoAdapter) {
			$this->setAdapter($adapter);
			parent::__construct($options);
		} else {
			parent::__construct($adapter);
		}
	}
	
	/**
	 * Find a record or a dataset by the primary id or an array of the ids
	 * 
	 * @param  mixed $id The primary key or an array of the primary keys
	 * @return ModelInterface | Collection | Iterator
	 */
	public function findByPrimaryId($id) {
		$primary = $this->getPrimaryName();
		
		if ($primary === false) {
			throw new OperationError('Unable to determine the primary identity');
		}
		
		// Many
		if (is_array($id)) {
			if ($primary === self::DEFAULT_PRIMARY) {
				$id = array_map([$this, 'normalizeId'], $id);
			}
			
			return $this->find([$primary => [
				'$in' => $id
			]]);
		}
		
		// One
		if ($primary === self::DEFAULT_PRIMARY) {
			$id = $this->normalizeId($id);
		}
		
		return $this->findFirst([$primary => $id]);
	}
	
	/**
	 * Save the model or the collection of the models
	 * 
	 * @param  ModelInterface $model
	 * @param  array          $options
	 * @return bool True if succeeded
	 */
	public function save(ModelInterface $model, array $options = []) {
		if ($model->isEmpty()) {
			throw new OperationError('Model has no data');
		}
		
		if ($model instanceof Model) {
			return $this->saveModel($model, $options);
		}
		
		
		if ($model instanceof Collection) {
			return $this->saveCollection($model, $options);
		}
		
		throw new OperationError('Unknown instance of the "ModelInterface" was given: "' . get_class($model) . '"');
	}
	
	/**
	 * Save the model
	 * 
	 * @param  Model $model
	 * @param  array $options
	 * @return bool
	 */
	private function saveModel(Model $model, array $options = []) {
		if ($model->isInsertForced()) {
			unset($model->_id);
			$model->unforceInsert();
		}
		
		// Update
		if (isset($model->_id)) {
			$data = $model->toSave();
			unset($data['_id']);

			$id = $this->normalizeId($model->_id);
			
			return $this->getAdapter()->update(
				$this->getTableName(),
				['$set' => $data],
				['_id'  => $id],
				$options
			);
		}
		
		// Insert
		return $this->getAdapter()->insert(
			$this->getTableName(),
			$model->toSave(), 
			$options
		);
	}
	
	/**
	 * Save the collection
	 * 
	 * @param  Collection $collection
	 * @param  array $options
	 * @return bool
	 */
	private function saveCollection(Collection $collection, array $options = []) {
		if ($collection->isInsertForced()) {
			unset($collection->_id);
			$collection->unforceInsert();
		}
		
		$result = true;
		
		foreach ($collection as $model) {
			if (! $this->saveModel($model, $options)) {
				$result = false;
			}
		}
		
		return $result;
	}

	/**
	 * Remove the record or the many of records by the model or the collection of the models
	 * 
	 * @param  ModelInterface $model
	 * @param  array          $options
	 * @return bool True if succeeded
	 * @throws OperationError
	 */
	public function remove(ModelInterface $model, array $options = []) {
		if ($model->isEmpty()) {
			throw new OperationError('Model has no data');
		}
		
		if ($model instanceof Model) {
			return $this->removeModel($model, $options);
		}
		
		
		if ($model instanceof Collection) {
			return $this->removeCollection($model, $options);
		}
		
		throw new OperationError('Unknown instance of the "ModelInterface" was given: "' . get_class($model) . '"');
	}
	
	/**
	 * Remove the model
	 * 
	 * @param  Model $model
	 * @param  array $options
	 * @return bool
	 * @throws OperationError
	 */
	private function removeModel(Model $model, array $options = []) {
		if (! isset($model->_id)) {
			throw new OperationError('The model has no ID');
		}
		
		$id = $this->normalizeId($model->_id);
		
		return $this->getAdapter()->remove(
			$this->getTableName(),
			['_id' => $id],
			$options
		);
	}
	
	/**
	 * Remove the collection
	 * 
	 * @param  Collection $collection
	 * @param  array $options
	 * @return bool
	 * @throws OperationError
	 */
	private function removeCollection(Collection $collection, array $options = []) {
		$ids = $collection->_id;
		
		if (count($ids) !== $collection->count()) {
			throw new OperationError('Some of the models has no ID');
		}
		
		$ids = array_map([$this, 'normalizeId'], $ids);
		
		return $this->getAdapter()->remove(
			$this->getTableName(),
			['_id' => [
				'$in' => $ids
			]],
			$options
		);
	}
	
	/**
	 * Find a first record
	 * 
	 * @param  mixed $criteria Query criteria
	 * @param  array $fields   Selected fields
	 * @return Model | null
	 */
	public function findFirst($criteria = [], array $fields = []) {
		$result = $this->getAdapter()->findFirst(
			$this->getTableName(),
			$criteria,
			$fields
		);
		
		if ($result === null) {
			return;
		}
		
		return $this->createResult($result);
	}
	
	/**
	 * Find all records of a data
	 * 
	 * @param  mixed $criteria Query criteria
	 * @param  array $fields   Selected fields
	 * @return Collection | Iterator
	 */
	public function find($criteria = [], array $fields = []) {
		$iterator = $this->getAdapter()->find(
			$this->getTableName(),
			$criteria,
			$fields
		);
		
		return $this->createResultset($iterator);
	}
	
	/**
	 * Fetch an items from a database and return as paginator
	 * 
	 * @param  mixed $criteria Query criteria
	 * @param  array $fields   Selected fields
	 * @return Paginator
	 */
	public function getPaginator($criteria = [], array $fields = []) {
		$iterator = $this->getAdapter()->find(
			$this->getTableName(),
			$criteria,
			$fields
		);
		
		return new Paginator(new PaginatorAdapter($iterator, $this));
	}
	
	/**
	 * Fetch an items from a database and return as iterator 
	 * 
	 * @param  mixed $criteria Query criteria
	 * @param  array $fields   Selected fields
	 * @return Iterator
	 */
	public function getIterator($criteria = [], array $fields = []) {
		$iterator = $this->getAdapter()->find(
			$this->getTableName(),
			$criteria,
			$fields
		);
		
		return $this->createIterator($iterator);
	}
	
	/**
	 * Insert an item into a database
	 * 
	 * @param  mixed $data
	 * @param  array $options
	 * @return Model
	 */
	public function insert(array $data, array $options = []) {
		$this->getAdapter()->insert(
			$this->getTableName(),
			$data,
			$options
		);
		
		return $this->createResult($data);
	}
	
	/**
	 * Delete an items from a database
	 * 
	 * @param  array $criteria
	 * @param  array $options
	 * @return bool
	 */
	public function delete(array $criteria = [], array $options = []) {
		return $this->getAdapter()->remove(
			$this->getTableName(),
			$criteria,
			$options
		);
	}
	
	/**
	 * Update some items of a database
	 * 
	 * @param  array $data
	 * @param  array $criteria
	 * @param  array $options
	 * @return bool
	 */
	public function update(array $data, array $criteria = [], array $options = []) {
		return $this->getAdapter()->update(
			$this->getTableName(),
			$criteria,
			$data,
			$options
		);
	}
	
	/**
	 * Get the mongo collection
	 * 
	 * @return \MongoCollection
	 */
	public function getCollection() {
		$name = $this->getTableName();
		
		return $this->getAdapter()->getCollection($name);
	}
	
	/**
	 * Normalize the ID
	 * 
	 * @param  MongoId | string $id
	 * @return MongoId
	 */
	protected function normalizeId($id) {
		if ($id instanceof MongoId) {
			return $id;
		}

		return new MongoId($id);
	}
	
	/**
	 * Set a database adapter
	 * 
	 * @param MongoAdapter $adapter
	 */
	public function setAdapter(MongoAdapter $adapter) {
		$this->_adapter = $adapter;
	}
	
	/**
	 * Get a database adapter
	 * 
	 * @return MongoAdapter
	 */
	public function getAdapter() {
		if ($this->_adapter === null) {
			if ($this->hasLocator()) {
				$this->_adapter = $this->getLocator()->get($this->getAdapterName());
			} else {
				throw new NoAdapter('Nor a database adapter neither a services locator has been provided');
			}
		}
		
		return $this->_adapter;
	}
	
}