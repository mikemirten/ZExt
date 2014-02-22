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

namespace ZExt\Paginator;

use ZExt\Paginator\Adapter\AdapterInterface,
    ZExt\Paginator\Control\ControlHandlerInterface;

use IteratorAggregate, Countable;

/**
 * Interface of a paginator
 * 
 * @category   ZExt
 * @package    Paginator
 * @subpackage Paginator
 * @author     Mike.Mirten
 * @version    1.1
 * 
 * @method int Count() Get the number of an items of the current page
 */
interface PaginatorInterface extends IteratorAggregate, Countable {
	
	// Pagination control data items
	const CONTROL_FIRST    = 0;
	const CONTROL_LAST     = 1;
	const CONTROL_PREVIOUS = 2;
	const CONTROL_NEXT     = 3;
	const CONTROL_RANGE    = 4;
	const CONTROL_CURRENT  = 5;
	
	/**
	 * Set the data adapter
	 * 
	 * @param AdapterInterface $adapter
	 */
	public function setAdapter(AdapterInterface $adapter);
	
	/**
	 * Get the data adapter
	 * 
	 * @return AdapterInterface
	 */
	public function getAdapter();
	
	/**
	 * Has been a data adapter provided
	 * 
	 * @return bool
	 */
	public function hasAdapter();
	
	/**
	 * Set the pagination control handler
	 * 
	 * @param ControlHandlerInterface $control
	 */
	public function setControlHandler(ControlHandlerInterface $control);
	
	/**
	 * Get the pagination control handler
	 * 
	 * @return ControlHandlerInterface
	 */
	public function getControlHandler();
	
	/**
	 * Has been a pagination control handler provided
	 * 
	 * @return bool
	 */
	public function hasControlHandler();
	
	/**
	 * Set the current page number
	 * 
	 * @param int $page
	 */
	public function setCurrentPage($page);
	
	/**
	 * Get the current page number
	 * 
	 * @return int
	 */
	public function getCurrentPage();
	
	/**
	 * Set the items number per a page
	 * 
	 * @param int $number
	 */
	public function setItemsPerPage($number);
	
	/**
	 * Get the items number per a page
	 * 
	 * @return int
	 */
	public function getItemsPerPage();
	
	/**
	 * Get the entire pages number
	 * 
	 * @return int
	 */
	public function getPagesNumber();
	
	/**
	 * Get the entire items number
	 * 
	 * @retirn int
	 */
	public function getItemsNumber();
	
	/**
	 * Get an items of the current page
	 * 
	 * @return \Traversable
	 */
	public function getCurrentItems();
	
	/**
	 * Get the pagination control data
	 * 
	 * @return array
	 */
	public function getPaginationControl();
	
	/**
	 * Is the paginator data empty ?
	 * 
	 * @return bool
	 */
	public function isEmpty();
	
	/**
	 * Set the name of the page param
	 * 
	 * @param string $name
	 */
	public function setPageParam($name);
	
	/**
	 * Set the name of the page param
	 * 
	 * @return string
	 */
	public function getPageParam();
	
}