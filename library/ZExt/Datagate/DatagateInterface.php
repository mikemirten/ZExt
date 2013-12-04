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

use ZExt\Model\ModelInterface;
use ZExt\Model\Collection;

/**
 * Interface of a gateway to a data and a data to a model mapper
 * 
 * @category   ZExt
 * @package    Datagate
 * @subpackage Datagate
 * @author     Mike.Mirten
 * @version    1.0 
 */
interface DatagateInterface {

	// Types of an item of a data
	const RESULT_OBJECT = 1;
	const RESULT_MODEL  = 2;
	const RESULT_ARRAY  = 4;

	// Types of a resultset of a data
	const RESULTSET_ITERATOR   = 8;
	const RESULTSET_COLLECTION = 16;
	const RESULTSET_ARRAY      = 32;

	/**
	 * Find a record or a dataset by the id or an array of the ids
	 * 
	 * @param  mixed $id The primary key or an array of the primary keys
	 * @return ModelInterface | Collection | Iterator
	 */
	public function find($id);

	/**
	 * Find a first record
	 * 
	 * @param  mixed $criteria Query criteria
	 * @return ModelInterface
	 */
	public function findFirst($criteria = null);

	/**
	 * Find all records of a data
	 * 
	 * @param  mixed $criteria Query criteria
	 * @return Collection | Iterator
	 */
	public function findAll($criteria = null);

	/**
	 * Save the model or the collection of the models
	 * 
	 * @param ModelInterface | Collection $model
	 */
	public function save(ModelInterface $model);

	/**
	 * Remove the record or the many of records by the model or the collection of the models
	 * 
	 * @param ModelInterface | Collection $model
	 */
	public function remove(ModelInterface $model);

	/**
	 * Create a new model, empty or with the supplied data
	 * 
	 * @param  array $data initial data for the the model
	 * @return ModelInterface
	 */
	public function create(array &$data = null);

	/**
	 * Create a new collection, empty or with the supplied data
	 * 
	 * @param  array $data initial data for the collection
	 * @return Collection
	 */
	public function createCollection(array &$data = null);

	/**
	 * Set the model's class
	 * 
	 * @param string $name
	 */
	public function setModelClass($class);

	/**
	 * Get model's class
	 * 
	 * @return string
	 */
	public function getModelClass();

	/**
	 * Set the collection's class
	 * 
	 * @param string $name
	 */
	public function setCollectionClass($class);

	/**
	 * Get collection's class
	 * 
	 * @return string
	 */
	public function getCollectionClass();

	/**
	 * Set the name of the table or collection
	 * 
	 * @param string $name
	 */
	public function setTableName($name);

	/**
	 * Get the name of the table or collection
	 * 
	 * @return string
	 */
	public function getTableName();
	
	/**
	 * Set the type of an item of a data
	 * See the RESULT_* & RESULTSET_* constants of the interface
	 * 
	 * @param int $type
	 */
	public function setResultType($type);

	/**
	 * Get the type of an item of a data
	 * 
	 * @return int $type
	 */
	public function getResultType();

}