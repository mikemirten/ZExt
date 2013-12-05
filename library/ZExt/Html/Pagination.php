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

namespace ZExt\Html;

use ZExt\Paginator\PaginatorInterface;
use Closure;

/**
 * Pagination control based on the navigation
 * 
 * @category   ZExt
 * @package    Html
 * @subpackage Pagination
 * @author     Mike.Mirten
 * @version    1.0
 */
class Pagination extends Navigation {
	
	/**
	 * Paginator
	 * 
	 * @var PaginatorInterface
	 */
	protected $_paginator;
	
	/**
	 * Number of a page parameter's name
	 * 
	 * @var string
	 */
	protected $_pageParam = 'page';
	
	/**
	 * Common url parameters
	 * 
	 * @var array
	 */
	protected $_urlParams = [];
	
	/**
	 * Url assembler
	 *
	 * @var Closure
	 */
	protected $_urlAssembler;
	
	/**
	 * Base url
	 *
	 * @var string
	 */
	protected $_baseUrl = '/';


	// Navigation elements
	protected $_useFirstLast = true;
	protected $_usePrevNext  = true;
	protected $_useRange     = true;
	
	// Elements strings
	protected $_stringFirst = '«';
	protected $_stringLast  = '»';
	protected $_stringPrev  = '←';
	protected $_stringNext  = '→';
	
	/**
	 * Constructor
	 * 
	 * @param PaginatorInterface $paginator
	 * @param Closure            $urlAssembler
	 */
	public function __construct(PaginatorInterface $paginator = null, Closure $urlAssembler = null) {
		parent::__construct();
		
		if ($paginator !== null) {
			$this->setPaginator($paginator);
		}
		
		if ($urlAssembler !== null) {
			$this->setUrlAssembler($urlAssembler);
		}
	}
	
	/**
	 * Set the paginator
	 * 
	 * @param  PaginatorInterface $paginator
	 * @return Pagination
	 */
	public function setPaginator(PaginatorInterface $paginator) {
		$this->_paginator = $paginator;
		
		return $this;
	}
	
	/**
	 * Get the paginator
	 * 
	 * @return PaginatorInterface
	 */
	public function getPaginator() {
		return $this->_paginator;
	}
	
	/**
	 * Has a paginator
	 * 
	 * @return bool
	 */
	public function hasPaginator() {
		return $this->_paginator !== null;
	}
	
	/**
	 * Set the url assembler callback
	 * 
	 * @param  Closure
	 * @return Pagination
	 */
	public function setUrlAssembler(Closure $assembler) {
		$this->_urlAssembler = $assembler;
		
		return $this;
	}
	
	/**
	 * Set using the "first" and the "last" navigation elements
	 * 
	 * @param  bool $flag
	 * @return Pagination
	 */
	public function setUseFirstLast($flag = true) {
		$this->_useFirstLast = (bool) $flag;
		
		return $this;
	}
	
	/**
	 * Set using the "Previous" and the "Next" navigation elements
	 * 
	 * @param  bool $flag
	 * @return Pagination
	 */
	public function setUsePrevNext($flag = true) {
		$this->_usePrevNext = (bool) $flag;
		
		return $this;
	}
	
	/**
	 * Set using the pages range navigation elements
	 * 
	 * @param  bool $flag
	 * @return Pagination
	 */
	public function setUseRange($flag = true) {
		$this->_useRange = (bool) $flag;
		
		return $this;
	}
	
	/**
	 * Set the page param
	 * 
	 * @param string $param
	 * @return Pagination
	 */
	public function setPageParam($param) {
		$this->_pageParam = (string) $param;
		
		return $this;
	}
	
	/**
	 * Get the page param
	 * 
	 * @return string
	 */
	public function getPageParam() {
		return $this->_pageParam;
	}
	
	/**
	 * Set a common url params
	 * 
	 * @param array $params
	 * @return Pagination
	 */
	public function setUrlParams(array $params) {
		foreach ($params as $param => $value) {
			$this->setUrlParam($param, $value);
		}
		
		return $this;
	}
	
	/**
	 * Get a common url params
	 * 
	 * @return array
	 */
	public function getUrlParams() {
		return $this->_urlParams;
	}
	
	/**
	 * Set a common url param
	 * 
	 * @param string $param
	 * @param mixed $value
	 * @return Pagination
	 */
	public function setUrlParam($param, $value) {
		$this->_urlParams[$param] = $value;
		
		return $this;
	}
	
	/**
	 * Set a common url param
	 * 
	 * @param string $param
	 * @return mixed
	 */
	public function getUrlParam($param) {
		if (isset($this->_urlParams[$param])) {
			return $this->_urlParams[$param];
		}
	}
	
	/**
	 * Has a common url param
	 * 
	 * @param  string $param
	 * @return bool
	 */
	public function hasUrlParam($param) {
		return isset($this->_urlParams[$param]);
	}
	
	/**
	 * Set the base url
	 * 
	 * @param  string $url
	 * @return Pagination
	 */
	public function setBaseUrl($url) {
		$this->_baseUrl = rtrim($url, '/') . '/';
		
		return $this;
	}
	
	/**
	 * Get the base url
	 * 
	 * @return string
	 */
	public function getBaseUrl() {
		return $this->_baseUrl;
	}
	
	/**
	 * Render the pagination
	 * 
	 * @return string
	 */
	public function render($html = null) {
		$control = $this->getPaginator()->getPaginationControl();
		
		// First
		if ($this->_useFirstLast && isset($control[PaginatorInterface::CONTROL_FIRST])) {
			$this->addElement(
				$this->_renderUrl($control[PaginatorInterface::CONTROL_FIRST]),
				$this->_stringFirst,
				'page-first'
			);
		}
		
		// Previous
		if ($this->_usePrevNext && isset($control[PaginatorInterface::CONTROL_PREVIOUS])) {
			$this->addElement(
				$this->_renderUrl($control[PaginatorInterface::CONTROL_PREVIOUS]),
				$this->_stringPrev,
				'page-previous'
			);
		}
		
		// Range
		if ($this->_useRange && isset($control[PaginatorInterface::CONTROL_RANGE])) {
			foreach ($control[PaginatorInterface::CONTROL_RANGE] as $element) {
				$this->addElement($this->_renderUrl($element), $element, 'page-' . $element);
			}
		}
		
		// Next
		if ($this->_usePrevNext && isset($control[PaginatorInterface::CONTROL_NEXT])) {
			$this->addElement(
				$this->_renderUrl($control[PaginatorInterface::CONTROL_NEXT]),
				$this->_stringNext,
				'page-next'
			);
		}
		
		// Last
		if ($this->_useFirstLast && isset($control[PaginatorInterface::CONTROL_LAST])) {
			$this->addElement(
				$this->_renderUrl($control[PaginatorInterface::CONTROL_LAST]),
				$this->_stringLast,
				'page-last'
			);
		}
		
		if (isset($control[PaginatorInterface::CONTROL_CURRENT])) {
			$this->setActive('page-' . $control[PaginatorInterface::CONTROL_CURRENT]);
		}
		
		return parent::render();
	}
	
	/**
	 * Render the url
	 * 
	 * @param  mixed $control
	 * @return string
	 */
	protected function _renderUrl($control) {
		$params = $this->getUrlParams();
		$params[$this->_pageParam] = $control;
		
		if ($this->_urlAssembler === null) {
			return $this->_baseUrl . '?' . http_build_query($params);
		}
		
		return $this->_urlAssembler->__invoke($params);
	}
	
}