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

namespace ZExt\Paginator\Control;

use ZExt\Paginator\PaginatorInterface;

/**
 * Sliding control handler
 * 
 * @category   ZExt
 * @package    Paginator
 * @subpackage Control
 * @author     Mike.Mirten
 * @version    1.0
 */
class Sliding implements ControlHandlerInterface {
	
	/**
	 * Number of the pages controls
	 *
	 * @var int
	 */
	protected $_rangeSize = 10;
	
	/**
	 * Set the number of the pages controls
	 * 
	 * @param int $size
	 */
	public function setPagesRangeSize($size) {
		$this->_rangeSize = (int) $size;
	}
	
	/**
	 * Get the pagination control data
	 * 
	 * @param  PaginatorInterface $paginator
	 * @return array
	 */
	public function getPaginationControl(PaginatorInterface $paginator) {
		$currentPage = $paginator->getCurrentPage();
		$pagesNumber = $paginator->getPagesNumber();
		
		if ($currentPage > $pagesNumber) {
			$currentPage = $pagesNumber;
		}
		
		if ($pagesNumber > $this->_rangeSize) {
			$rangeMiddle = (int) ceil($this->_rangeSize / 2);
			
			if ($currentPage - $rangeMiddle > $pagesNumber - $this->_rangeSize) {
				$lower = $pagesNumber - $this->_rangeSize + 1;
				$upper = $pagesNumber;
			} else {
				if ($currentPage - $rangeMiddle < 0) {
					$rangeMiddle = $currentPage;
				}

				$offset = $currentPage - $rangeMiddle;
				$lower  = $offset + 1;
				$upper  = $offset + $this->_rangeSize;
			}
		} else {
			$lower = 1;
			$upper = $pagesNumber;
		}
		
		$range = [];
		
		for ($i = $lower; $i <= $upper; ++ $i) {
			$range[] = $i;
		}
		
		$data = [
			PaginatorInterface::CONTROL_CURRENT => $currentPage,
			PaginatorInterface::CONTROL_RANGE   => $range
		];
		
		if ($currentPage > 2) {
			$data[PaginatorInterface::CONTROL_FIRST] = 1;
		}
		
		if ($pagesNumber > $currentPage + 1) {
			$data[PaginatorInterface::CONTROL_LAST] = $pagesNumber;
		}
		
		if ($currentPage > 1) {
			$data[PaginatorInterface::CONTROL_PREVIOUS] = $currentPage - 1;
		}
		
		if ($currentPage < $pagesNumber) {
			$data[PaginatorInterface::CONTROL_NEXT] = $currentPage + 1;
		}
		
		return $data;
	}
	
}