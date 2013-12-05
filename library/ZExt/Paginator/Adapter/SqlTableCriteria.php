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

namespace ZExt\Paginator\Adapter;

use ZExt\Datagate\Criteria\CriteriaInterface;

/**
 * Criteria data adapter
 * 
 * @category   ZExt
 * @package    Paginator
 * @subpackage Adapter
 * @author     Mike.Mirten
 * @version    1.0
 */
class SqlTableCriteria implements AdapterInterface {
	
	/**
	 * Instance of a criteria
	 *
	 * @var CriteriaInterface
	 */
	protected $criteria;
	
	/**
	 * Constructor
	 * 
	 * @param CriteriaInterface $criteria
	 */
	public function __construct(CriteriaInterface $criteria = null) {
		if ($criteria !== null) {
			$this->setCriteria($criteria);
		}
	}
	
	/**
	 * Set the criteria
	 * 
	 * @param CriteriaInterface $criteria
	 */
	public function setCriteria(CriteriaInterface $criteria) {
		$this->criteria = $criteria;
	}
	
	/**
	 * Get the criteria
	 * 
	 * @return CriteriaInterface
	 */
	public function getCriteria() {
		return $this->criteria;
	}
	
	/**
	 * Get an items of the page
	 * 
	 * @param  int $offset
	 * @param  int $limit
	 * @return \Traversable
	 */
	public function getItems($offset, $limit) {
		return $this->getCriteria()
			->limit($limit, $offset)
			->findAll();
	}
	
	/**
	 * Count the total items number
	 * 
	 * @return int
	 */
	public function count() {
		$criteria = clone $this->getCriteria();
		
		$result = $criteria->limit(0, 0)
		         ->columns(['count' => 'COUNT(*)'])
		         ->findFirst();
		
		return (int) $result->count;
	}
	
}