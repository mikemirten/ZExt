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

/**
 * Query criteria interface
 * 
 * @category   ZExt
 * @package    Datagate
 * @subpackage Criteria
 * @author     Mike.Mirten
 * @version    1.0
 */
interface CriteriaInterface {
	
	/**
	 * From which table or collection
	 * 
	 * @param  string | array $table
	 * @param  string | array $columns
	 * @return CriteriaInterface
	 */
	public function from($table, $columns = null);
	
	/**
	 * Add the columns or properties
	 * 
	 * @param  string | array $columns
	 * @return CriteriaInterface
	 */
	public function columns($columns);
	
	/**
	 * Add the "where" condition
	 * 
	 * @param  string $condition
	 * @param  mixed  $value
	 * @param  int    $type
	 * @return CriteriaInterface
	 */
	public function where($condition, $value = null, $type = null);
	
	/**
	 * Add the "or where" condition
	 * 
	 * @param  string $condition
	 * @param  mixed  $value
	 * @param  int    $type
	 * @return CriteriaInterface
	 */
	public function orWhere($condition, $value = null, $type = null);
	
	/**
	 * Set the limit of a rows or documents
	 * 
	 * @param  int $limit
	 * @param  int $offset
	 * @return CriteriaInterface
	 */
	public function limit($limit, $offset = null);
	
	/**
	 * Set the offset on rows or documents
	 * 
	 * @param  int $offset
	 * @return CriteriaInterface
	 */
	public function offset($offset);
	
	/**
	 * Add the sort by the column or property
	 * 
	 * @param  string | array $columns
	 * @return CriteriaInterface
	 */
	public function sort($columns);
	
	/**
	 * Find all by the query
	 * 
	 * @return mixed
	 */
	public function findAll();
	
	/**
	 * Find a first by the query
	 * 
	 * @return mixed
	 */
	public function findFirst();
	
}