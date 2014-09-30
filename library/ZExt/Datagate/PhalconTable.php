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

use Phalcon\Db\AdapterInterface,
    Phalcon\DiInterface,
    Phalcon\Di;

use Phalcon\Mvc\Model\Criteria            as PhalconCriteria,
    Phalcon\Mvc\Model\Manager             as ModelsManager,
    Phalcon\Mvc\Model\Transaction\Manager as TransactionsManager,
    Phalcon\Mvc\Model\Metadata\Memory     as MetaData,
    Phalcon\Mvc\Model\Resultset\Simple    as ResultsetSimple,
    Phalcon\Mvc\Model\Row;

use ZExt\Datagate\Criteria\PhalconCriteria as Criteria,
    ZExt\Datagate\Phalcon\Model            as PhalconModel,
    ZExt\Model\Collection,
    ZExt\Model\Model;

use ZExt\Paginator\Paginator,
    ZExt\Paginator\Adapter\SqlTableCriteria as AdapterCriteria;

use ZExt\Datagate\Exceptions\NoAdapter,
    ZExt\Datagate\Exceptions\InvalidCriteria;

/**
 * Phalcon model based datagate
 * 
 * @category   ZExt
 * @package    Datagate
 * @subpackage Datagate
 * @author     Mike.Mirten
 * @version    1.1beta
 */
class PhalconTable extends DatagateAbstract {
	
	// Phalcon dependencies names
	const PHSRV_ADAPTER      = 'db';
	const PHSRV_TRANSACTIONS = 'transactionsManager';
	const PHSRV_MANAGER      = 'modelsManager';
	const PHSRV_META         = 'modelsMetadata';
	
	/**
	 * Shared table models DI option
	 *
	 * @var bool
	 */
	private static $_allowSharedModelDi = true;
	
	/**
	 * Shared table models DI
	 *
	 * @var DiInterface 
	 */
	private static $_sharedModelsDi;
	
	/**
	 * Database adapter name
	 * 
	 * Can be overrided by an user
	 *
	 * @var string
	 */
	protected $adapter = self::PHSRV_ADAPTER;
	
	/**
	 * Phalcon DB adapter
	 *
	 * @var AdapterInterface
	 */
	private $_adapter;
	
	/**
	 * Phalcon DB table model
	 *
	 * @var PhalconModel 
	 */
	private $_model;
	
	/**
	 * Dependency injector for a model
	 *
	 * @var DiInterface
	 */
	private $_modelDi;
	
	/**
	 *
	 * @var type 
	 */
	private $_transactionsManager;
	
	/**
	 * Set the "Shared phalcon table models DI" option
	 * 
	 * @param bool $option
	 */
	static public function allowSharedTableModelDi($option = true) {
		self::$_sharedModelsDi = (bool) $option;
	}
	
	/**
	 * Constructor
	 * 
	 * @param AdapterInterface     $adapter Adapter instance or a services locator or an options
	 * @param array | \Traversable $options
	 */
	public function __construct($adapter = null, $options = null) {
		if ($adapter instanceof AdapterInterface) {
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
	 * @return Model | Collection | Iterator
	 */
	public function findByPrimaryId($id) {
		if (is_array($id)) {
			$primaty  = $this->getPrimaryName();
			$criteria = $this->getTableModel()->query()->inWhere($primaty, $id);
			
			return $this->find($criteria);
		}
		
		return $this->findFirst($id);
	}
	
	/**
	 * Find a first record
	 * 
	 * @param  mixed $criteria Query criteria
	 * @return Model
	 */
	public function findFirst($criteria = null) {
		$criteria = $this->normalizeCriteria($criteria);
		$result   = $this->getTableModel()->findFirst($criteria);
		
		if ($result === false) {
			return;
		}
		
		if ($result instanceof Row) {
			$result = (array) $result;
		} else {
			$result = $result->toArray();
		}
		
		return $this->createResult($result);
	}

	/**
	 * Find all records of a data
	 * 
	 * @param  mixed $criteria Query criteria
	 * @return Collection | Iterator
	 */
	public function find($criteria = null) {
		$criteria = $this->normalizeCriteria($criteria);
		$result   = $this->getTableModel()->find($criteria);
		
		if ($result->count() === 0) {
			return;
		}
		
		if ($this->getResultType() & self::RESULT_OBJECT) {
			$result->setHydrateMode(ResultsetSimple::HYDRATE_OBJECTS);
		} else {
			$result->setHydrateMode(ResultsetSimple::HYDRATE_ARRAYS);
		}
		
		return $this->createResultset($result);
	}
	
	/**
	 * Normalize the type of a criteria
	 * 
	 * @param  mixed $criteria
	 * @return array | string | int | null
	 * @throws InvalidCriteria
	 */
	protected function normalizeCriteria($criteria) {
		if ($criteria === null) {
			return;
		}
		
		if ($criteria instanceof Criteria) {
			return $criteria->getInnerCriteria()->getParams();
		}
		
		if ($criteria instanceof PhalconCriteria) {
			return $criteria->getParams();
		}
		
		if (is_array($criteria) || is_string($criteria) || is_int($criteria)) {
			return $criteria;
		}
		
		throw new InvalidCriteria('Invalid type of the criteria: "' . gettype($criteria) . '"');
	}

	/**
	 * Save the model
	 * 
	 * @param  Model $model
	 * @return bool
	 */
	protected function saveModel(Model $model) {
		return $this->saveThroughPhalconModel($model);
	}
	
	/**
	 * Save the collection
	 * 
	 * @param  Collection $collection
	 * @return bool
	 */
	protected function saveCollection(Collection $collection) {
		if ($collection->count() === 1) {
			return $this->save($collection->getFirst());
		}

		$transaction = $this->getTransactionsManager()->get(true);

		foreach ($collection as $model) {
			if (! $this->saveThroughPhalconModel($model, $transaction)) {
				$transaction->rollback();
			}
		}

		$transaction->commit();
		return true;
	}
	
	/**
	 * Save the model
	 * 
	 * @param  Model $model
	 * @return bool  True if succeeded
	 */
	private function saveThroughPhalconModel(Model $model, $transaction = null) {
		$primary      = $this->getPrimaryName();
		$phalconModel = $this->createTableModel($model);
		
		if ($transaction !== null) {
			$phalconModel->setTransaction($transaction);
		}
		
		// If the primary is unresolvable, let's phalcon try it
		if ($primary === null) {
			return $phalconModel->save();
		}

		// Insert or Update resolving
		$isInsert = $model->isInsertForced();

		if (is_array($primary)) {
			foreach ($primary as $part) {
				if (! isset($model->$part)) {
					$isInsert = false;
				}
			}
		} else if (is_string($primary) && ! isset($model->$primary)) {
			$isInsert = true;
		}

		// Do the insert
		if ($isInsert) {
			$result = $phalconModel->create();
			$model->unforceInsert();

			// Put the primary data into the model after inserting
			if (is_array($primary)) {
				foreach ($primary as $part) {
					if (isset($phalconModel->$part)) {
						$model->$part = $phalconModel->$part;
					}
				}
			} else if (isset($phalconModel->$primary)) {
				$model->$primary = $phalconModel->$primary;
			}

			return $result;
		}

		// Do the update
		return $phalconModel->update();
	}
	
	/**
	 * Remove the model
	 * 
	 * @param  Model $model
	 * @return bool
	 */
	protected function removeModel(Model $model) {
		return $this->createTableModel($model)->delete();
	}
	
	/**
	 * Remove the collection
	 * 
	 * @param  Collection $collection
	 * @return bool
	 */
	protected function removeCollection(Collection $collection) {
		$transaction = $this->getTransactionsManager()->get(true);
			
		foreach ($collection as $model) {
			$phalconModel = $this->createTableModel($model);
			$phalconModel->setTransaction($transaction);

			if (! $phalconModel->delete()) {
				$transaction->rollback();
			}
		}

		$transaction->commit();
		return true;
	}
	
	/**
	 * Get the paginator
	 * 
	 * @param  Criteria $criteria
	 * @return Paginator
	 */
	public function getPaginator(Criteria $criteria = null) {
		if ($criteria === null) {
			$criteria = $this->query();
		}
		
		return new Paginator(new AdapterCriteria($criteria));
	}
	
	/**
	 * Get the query criteria
	 * 
	 * @return Criteria
	 */
	protected function query() {
		$criteria = $this->getTableModel()->query();
		
		return new Criteria($criteria, $this);
	}
	
	/**
	 * Get the query criteria (alias to the query())
	 * 
	 * @return Criteria
	 */
	protected function select() {
		return $this->query();
	}
	
	/**
	 * Get the name of the primary identity
	 * 
	 * @return string | array | null
	 */
	protected function getPrimaryName() {
		if ($this->primary === null) {
			$tableModel    = $this->getTableModel();
			$this->primary = $tableModel->getModelsMetaData()->getPrimaryKeyAttributes($tableModel);
			
			if (empty($this->primary)) {
				$this->primary = false;
			}
			
			if (count($this->primary) === 1) {
				$this->primary = current($this->primary);
			}
		}
		
		if ($this->primary === false) {
			return;
		}
		
		return $this->primary;
	}

	/**
	 * Set the phalcon database adapter
	 * 
	 * @param AdapterInterface $adapter
	 */
	public function setAdapter(AdapterInterface $adapter) {
		$this->_adapter = $adapter;
	}

	/**
	 * Get the phalcon database adapter
	 * 
	 * @return AdapterInterface
	 * @throws NoAdapter
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
	
	/**
	 * Set the transactions manager
	 * 
	 * @param TransactionsManager $manager
	 */
	public function setTransactionsNamager(TransactionsManager $manager) {
		$this->_transactionsManager = $manager;
	}
	
	/**
	 * Get the transactions manager
	 * 
	 * @return TransactionsManager
	 */
	public function getTransactionsManager() {
		if ($this->_transactionsManager === null) {
			if ($this->hasLocator()) {
				$locator = $this->getLocator();

				if ($locator->has(self::PHSRV_TRANSACTIONS)) {
					$this->_transactionsManager = $locator->get(self::PHSRV_TRANSACTIONS);
				}
			}
			
			if ($this->_transactionsManager === null) {
				$this->_transactionsManager = new TransactionsManager($this->getTableModelDi());
			}
			
			$this->_transactionsManager->setDbService($this->getAdapterName());
		}
		
		return $this->_transactionsManager;
	}
	
	/**
	 * Get the phalcon model instance
	 * 
	 * @return PhalconModel
	 */
	protected function getTableModel() {
		if ($this->_model === null) {
			$this->_model = $this->createTableModel();
		}
		
		return $this->_model;
	}
	
	/**
	 * Create the phalcon model instance
	 * 
	 * @param  Model $model
	 * @return PhalconModel
	 */
	protected function createTableModel(Model $model = null) {
		$phalconModel = new PhalconModel($this->getTableModelDi());
		$phalconModel->setDatagate($this);
		
		if ($model !== null) {
			$phalconModel->assign($model->getData());
		}
		
		return $phalconModel;
	}
	
	/**
	 * Set the dependency injector for the phalcon table model
	 * 
	 * @param DiInterface $di
	 */
	public function setTableModelDi(DiInterface $di) {
		$this->_modelDi = $di;
	}
	
	/**
	 * Get the dependency injector for the phalcon table model 
	 * If has not been supplied, trying to create the our own
	 * 
	 * @return DiInterface
	 */
	public function getTableModelDi() {
		if ($this->_modelDi !== null) {
			return $this->_modelDi;
		}
		
		if (self::$_allowSharedModelDi && self::$_sharedModelsDi !== null) {
			return self::$_sharedModelsDi;
		}
		
		// If has a locator, try to using it
		if ($this->hasLocator()) {
			$locator = $this->getLocator();
			
			// If has the models dependency injector in the locator
			if ($locator->has('modelsDi')) {
				$di = $locator->get('modelsDi');
				
				self::$_allowSharedModelDi = false;
			}
			// If not, create it
			else {
				$di = new Di();
				
				// If possible. pass the dependencies definitions
				if ($locator instanceof DiInterface) {
					if ($locator->has(self::PHSRV_MANAGER)) {
						$di->setRaw(self::PHSRV_MANAGER, $locator->getRaw(self::PHSRV_MANAGER));
					}

					if ($locator->has(self::PHSRV_META)) {
						$di->setRaw(self::PHSRV_META, $locator->getRaw(self::PHSRV_META));
					}
				}
				// If not, pass the dependencies instances
				else {
					if ($locator->has(self::PHSRV_MANAGER)) {
						$di->set(self::PHSRV_MANAGER, $locator->get(self::PHSRV_MANAGER), true);
					}

					if ($locator->has(self::PHSRV_META)) {
						$di->set(self::PHSRV_META, $locator->get(self::PHSRV_META), true);
					}
				}
			}
		} 
		// If not, create it
		else {
			$di = new Di();
		}

		// Making shure, that all the dependencies are present:
	
		if (! $di->has(self::PHSRV_ADAPTER)) {
			$di->set(self::PHSRV_ADAPTER, function() {
				return $this->getAdapter();
			}, true);
		}
		
		if (! $di->has(self::PHSRV_MANAGER)) {
			$di->set(self::PHSRV_MANAGER, function() {
				return new ModelsManager();
			}, true);
		}

		if (! $di->has(self::PHSRV_META)) {
			$di->set(self::PHSRV_META, function() {
				return new MetaData();
			}, true);
		}
		
		if (self::$_allowSharedModelDi && self::$_sharedModelsDi === null) {
			self::$_sharedModelsDi = $di;
		}

		$this->_modelDi = $di;
		
		return $di;
	}

}