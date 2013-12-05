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

namespace ZExt\Datagate\Criteria;

use Phalcon\Mvc\Model\Criteria;
use ZExt\Datagate\DatagateInterface;

/**
 * Phalcon based query criteria
 * 
 * @category   ZExt
 * @package    Datagate
 * @subpackage Criteria
 * @author     Mike.Mirten
 * @version    1.0dev
 */
class PhalconCriteria implements CriteriaInterface {
	
	/**
	 * Phalcon query criteria
	 *
	 * @var Criteria 
	 */
	protected $_criteria;
	
	/**
	 * Datagate
	 *
	 * @var DatagateInterface
	 */
	protected $_datagate;
	
	/**
	 * Limit
	 *
	 * @var int
	 */
	protected $_limit = 0;
	
	/**
	 * Constructor
	 * 
	 * @param Criteria          $criteria
	 * @param DatagateInterface $datagate
	 */
	public function __construct(Criteria $criteria, DatagateInterface $datagate) {
		$this->_criteria = $criteria;
		$this->_datagate = $datagate;
	}
	
	/**
	 * From which table or collection
	 * 
	 * @param  string | array $table
	 * @param  string | array $columns
	 * @return PhalconCriteria
	 */
	public function from($table, $columns = null) {
		$this->_criteria->setModelName($table);
		
		if ($columns !== null) {
			$this->_criteria->columns($columns);
		}
		
		return $this;
	}
	
	/**
	 * Add the columns or properties
	 * 
	 * @param  string | array $columns
	 * @return PhalconCriteria
	 */
	public function columns($columns) {
		$this->_criteria->columns($columns);
		
		return $this;
	}
	
	/**
	 * Add the "where" condition
	 * 
	 * @param  string $condition
	 * @param  mixed  $value
	 * @param  int    $type
	 * @return PhalconCriteria
	 */
	public function where($condition, $value = null, $type = null) {
		$this->_criteria->andWhere($condition);
		
		return $this;
	}
	
	/**
	 * Add the "or where" condition
	 * 
	 * @param  string $condition
	 * @param  mixed  $value
	 * @param  int    $type
	 * @return PhalconCriteria
	 */
	public function orWhere($condition, $value = null, $type = null) {
		$this->_criteria->orWhere($condition);
		
		return $this;
	}
	
	/**
	 * Set the limit of a rows or documents
	 * 
	 * @param  int $limit
	 * @param  int $offset
	 * @return PhalconCriteria
	 */
	public function limit($limit, $offset = null) {
		$this->_criteria->limit($limit, $offset);
		$this->_limit = (int) $limit;
				
		return $this;
	}
	
	/**
	 * Set the offset on rows or documents
	 * 
	 * @param  int $offset
	 * @return PhalconCriteria
	 */
	public function offset($offset) {
		$this->_criteria->limit($this->_limit, $offset);
		
		return $this; 
	}
	
	/**
	 * Add the sort by the column or property
	 * 
	 * @param  string | array $columns
	 * @return PhalconCriteria
	 */
	public function sort($columns) {
		$this->_criteria->orgerBy($columns);
		
		return $this;
	}
	
	/**
	 * Find all by the query
	 * 
	 * @return mixed
	 */
	public function findAll() {
		return $this->_datagate->findAll($this);
	}
	
	/**
	 * Find a first by the query
	 * 
	 * @return mixed
	 */
	public function findFirst() {
		return $this->_datagate->findFirst($this);
	}
	
	/**
	 * Execute the query (Alias to the findAll())
	 * 
	 * @return mixed
	 */
	public function execute() {
		return $this->_datagate->findAll($this);
	}
	
	/**
	 * Get the phalcon query criteria
	 * 
	 * @return Criteria
	 */
	public function getInnerCriteria() {
		return $this->_criteria;
	}
	
	/**
	 * Clone the inner criteria
	 */
	public function __clone() {
		$this->_criteria = clone $this->_criteria;
	}
	
}