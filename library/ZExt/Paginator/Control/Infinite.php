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
 * Infinite data control handler
 * 
 * @category   ZExt
 * @package    Paginator
 * @subpackage Control
 * @author     Mike.Mirten
 * @version    1.0
 */
class Infinite implements ControlHandlerInterface {
	
	/**
	 * Get the pagination control data
	 * 
	 * @param  PaginatorInterface $paginator
	 * @return array
	 */
	public function getPaginationControl(PaginatorInterface $paginator) {
		$currentPage = $paginator->getCurrentPage();
		
		$data = [
			PaginatorInterface::CONTROL_CURRENT => $currentPage,
			PaginatorInterface::CONTROL_RANGE   => [$currentPage]
		];
		
		if ($currentPage > 2) {
			$data[PaginatorInterface::CONTROL_FIRST] = 1;
		}
		
		if ($currentPage > 1) {
			$data[PaginatorInterface::CONTROL_PREVIOUS] = $currentPage - 1;
		}
		
		if ($paginator->count() >= $paginator->getItemsNumberPerPage()) {
			$data[PaginatorInterface::CONTROL_NEXT] = $currentPage + 1;
		}
		
		return $data;
	}
	
}