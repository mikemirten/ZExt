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

use ZExt\Di\LocatorAwareInterface,
    ZExt\Di\LocatorAwareTrait;

use ZExt\Paginator\Adapter\AdapterInterface,
    ZExt\Paginator\Control\ControlHandlerInterface,
    ZExt\Paginator\Control\Sliding;

use Traversable, RuntimeException;

/**
 * Paginator
 * 
 * @category   ZExt
 * @package    Paginator
 * @subpackage Paginator
 * @author     Mike.Mirten
 * @version    1.1
 */
class Paginator implements PaginatorInterface, LocatorAwareInterface {
	
	use LocatorAwareTrait;
	
	/**
	 * Data adapter
	 * 
	 * @var AdapterInterface
	 */
	private $_adapter;
	
	/**
	 * Pagination control handler
	 *
	 * @var ControlHandlerInterface
	 */
	private $_controlHandler;
	
	/**
	 * Service id of a pagination control handler
	 *
	 * @var string
	 */
	protected $_controlHandlerServiceId = 'paginationControlHandler';
	
	/**
	 * Current page number
	 *
	 * @var int
	 */
	protected $_currentPage = 1;
	
	/**
	 * Page param
	 *
	 * @var string
	 */
	protected $_pageParam = 'page';
	
	/**
	 * Number of an items per a page
	 *
	 * @var int
	 */
	protected $_itemsPerPage = 20;
	
	/**
	 * Items of the current page
	 *
	 * @var array
	 */
	protected $_currentItems;
	
	/**
	 * Nubmer of the pages
	 *
	 * @var int
	 */
	protected $_entirePagesNumber;
	
	/**
	 * Iteration pointer
	 *
	 * @var int 
	 */
	protected $_pointer = 0;
	
	/**
	 * Constructor
	 * 
	 * @param AdapterInterface        $adapter Data adapter
	 * @param ControlHandlerInterface $handler Pagination control handler
	 */
	public function __construct(AdapterInterface $adapter = null, ControlHandlerInterface $handler = null) {
		if ($adapter !== null) {
			$this->setAdapter($adapter);
		}
		
		if ($handler !== null) {
			$this->setControlHandler($handler);
		}
	}
	
	/**
	 * Set the data adapter
	 * 
	 * @param  AdapterInterface $adapter
	 * @return Paginator
	 */
	public function setAdapter(AdapterInterface $adapter) {
		$this->_adapter = $adapter;
		
		$this->reset();
		
		return $this;
	}
	
	/**
	 * Get the data adapter
	 * 
	 * @return AdapterInterface
	 */
	public function getAdapter() {
		return $this->_adapter;
	}
	
	/**
	 * Has been a data adapter provided
	 * 
	 * @return bool
	 */
	public function hasAdapter() {
		return $this->_adapter !== null;
	}
	
	/**
	 * Set the pagination control handler
	 * 
	 * @param  ControlHandlerInterface $handler
	 * @return Paginator
	 */
	public function setControlHandler(ControlHandlerInterface $handler) {
		$this->_controlHandler = $handler;
		
		return $this;
	}
	
	/**
	 * Get the pagination control handler
	 * 
	 * @return ControlHandlerInterface
	 */
	public function getControlHandler() {
		if ($this->_controlHandler === null) {
			if ($this->hasLocator() && $this->getLocator()->has($this->_controlHandlerServiceId)) {
				$this->_controlHandler = $this->getLocator()->get($this->_controlHandlerServiceId);
				
				if (! $this->_controlHandler instanceof ControlHandlerInterface) {
					throw new RuntimeException('Pagination control handler must implement "ControlHandlerInterface"');
				}
			} else {
				$this->_controlHandler = new Sliding();
			}
		}
		
		return $this->_controlHandler;
	}
	
	/**
	 * Has been a pagination control handler provided
	 * 
	 * @return bool
	 */
	public function hasControlHandler() {
		return $this->_controlHandler !== null;
	}
	
	/**
	 * Set the service id of a pagination control handler
	 * 
	 * @param string $id
	 */
	public function setControlHandlerServiceId($id) {
		$this->_controlHandlerServiceId = (string) $id;
	}
	
	/**
	 * Set the current page number
	 * 
	 * @param  int $page
	 * @return Paginator
	 */
	public function setCurrentPage($page) {
		$this->_currentPage = (int) $page;
		
		if ($this->_currentPage < 1) {
			throw new RuntimeException('Page number can\'t be zero or negative');
		}
		
		$this->reset();
		
		return $this;
	}
	
	/**
	 * Get the current page number
	 * 
	 * @return int
	 */
	public function getCurrentPage() {
		return $this->_currentPage;
	}
	
	/**
	 * Set the items number per a page
	 * 
	 * @param  int $number
	 * @return Paginator
	 */
	public function setItemsPerPage($number) {
		$this->_itemsPerPage = (int) $number;
		
		$this->reset();
		
		return $this;
	}
	
	/**
	 * Get the items number per a page
	 * 
	 * @return int
	 */
	public function getItemsPerPage() {
		return $this->_itemsPerPage;
	}
	
	/**
	 * Get the entire pages number
	 * 
	 * @return int
	 */
	public function getPagesNumber() {
		if ($this->_entirePagesNumber === null) {
			$this->_entirePagesNumbe = (int) ceil($this->getAdapter()->count() / $this->_itemsPerPage);
		}
		
		return $this->_entirePagesNumbe;
	}
	
	/**
	 * Get the entire items number
	 * 
	 * @retirn int
	 */
	public function getItemsNumber() {
		return $this->getAdapter()->count();
	}
	
	/**
	 * Get an items of the current page
	 * 
	 * @return array
	 */
	public function getCurrentItems() {
		if ($this->_currentItems === null) {
			$offset = $this->_itemsPerPage * $this->_currentPage - $this->_itemsPerPage;
			
			$this->_currentItems = $this->getAdapter()->getItems($offset, $this->_itemsPerPage);
			
			if (! $this->_currentItems instanceof Traversable) {
				throw new RuntimeException('Data from an adapter must implement the "Traversable" interface');
			}
		}
		
		return $this->_currentItems;
	}
	
	/**
	 * Get the pagination control data
	 * @see PaginatorInterface
	 * 
	 * @return array
	 */
	public function getPaginationControl() {
		return $this->getControlHandler()->getPaginationControl($this);
	}
	
	/**
	 * Reset the paginator's data
	 */
	protected function reset() {
		$this->_entirePagesNumber = null;
		$this->_currentItems      = null;
		$this->_pointer           = 0;
	}
	
	/**
	 * Iterator aggregate
	 * 
	 * @return Traversable
	 */
	public function getIterator() {
		return $this->getCurrentItems();
	}
	
	/**
	 * Get the number of an items of the current page
	 * 
	 * @return int
	 */
	public function count() {
		return count($this->getCurrentItems());
	}
	
	/**
	 * Is the paginator data empty ?
	 * 
	 * @return bool
	 */
	public function isEmpty() {
		return count($this->getCurrentItems()) === 0;
	}
	
	/**
	 * Set the name of the page param
	 * 
	 * @param string $page
	 */
	public function setPageParam($name) {
		$this->_pageParam = (string) $name;
	}
	
	/**
	 * Set the name of the page param
	 * 
	 * @return string
	 */
	public function getPageParam() {
		return $this->_pageParam;
	}
	
}