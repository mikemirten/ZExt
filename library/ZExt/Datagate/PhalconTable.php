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

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Phalcon\Di;

use Phalcon\Mvc\Model\Criteria         as PhalconCriteria;
use Phalcon\Mvc\Model\Manager          as ModelsManager;
use Phalcon\Mvc\Model\Metadata\Memory  as MetaData;
use Phalcon\Mvc\Model\Resultset\Simple as ResultsetSimple;
use Phalcon\Mvc\Model\Row;

use ZExt\Datagate\Criteria\PhalconCriteria as Criteria;
use ZExt\Datagate\Phalcon\Model            as PhalconModel;
use ZExt\Model\ModelInterface;
use ZExt\Model\Collection;
use ZExt\Model\Model;

use ZExt\Paginator\Paginator;
use ZExt\Paginator\Adapter\SqlTableCriteria as AdapterCriteria;

use ZExt\Datagate\Exceptions\NoAdapter;
use ZExt\Datagate\Exceptions\InvalidCriteria;

/**
 * Phalcon model based datagate
 * 
 * @category   ZExt
 * @package    Datagate
 * @subpackage Datagate
 * @author     Mike.Mirten
 * @version    1.0dev
 */
class PhalconTable extends DatagateAbstract {
	
	// Phalcon dependencies names
	const PHSRV_ADAPTER = 'db';
	const PHSRV_MANAGER = 'modelsManager';
	const PHSRV_META    = 'modelsMetadata';
	
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
	 * Find a record or a dataset by the id or an array of the ids
	 * 
	 * @param  mixed $id The primary key or an array of the primary keys
	 * @return ModelInterface | Collection | Iterator
	 */
	public function find($id) {
		if (is_array($id)) {
			$primaty  = $this->getPrimaryName();
			$criteria = $this->getTableModel()->query()->inWhere($primaty, $id);
			
			return $this->findAll($criteria);
		} else {
			return $this->findFirst($id);
		}
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
	public function findAll($criteria = null) {
		$criteria = $this->normalizeCriteria($criteria);
		$result   = $this->getTableModel()->find($criteria);
		
		if ($result->count() === 0) {
			return;
		}
		
		$result->setHydrateMode(ResultsetSimple::HYDRATE_ARRAYS);
		
		return $this->createResultset($result);
	}
	
	/**
	 * Normalize the type of a criteria
	 * 
	 * @param  mixed $criteria
	 * @return array | string | int
	 * @throws InvalidCriteria
	 */
	protected function normalizeCriteria($criteria) {
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
	 * Save the model or the collection of the models
	 * 
	 * @param ModelInterface | Collection $model
	 */
	public function save(ModelInterface $model) {
		/**
		 * @todo write a save code
		 */
	}
	
	/**
	 * Remove the record or the many of records by the model or the collection of the models
	 * 
	 * @param ModelInterface | Collection $model
	 */
	public function remove(ModelInterface $model) {
		/**
		 * @todo write a remove code
		 */
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
				$this->_adapter = $this->getLocator()->get(self::PHSRV_ADAPTER);
			} else {
				throw new NoAdapter('Nor a database adapter neither a services locator has been provided');
			}
		}
		
		return $this->_adapter;
	}
	
	/**
	 * Get the phalcon model instance
	 * 
	 * @return PhalconModel
	 */
	protected function getTableModel() {
		if ($this->_model === null) {
			$model = new PhalconModel($this->getTableModelDi());
			$model->setDatagate($this);
			
			$this->_model = $model;
		}
		
		return $this->_model;
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