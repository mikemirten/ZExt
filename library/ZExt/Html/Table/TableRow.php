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

namespace ZExt\Html\Table;

use ZExt\Html\MultiElementsAbstract;
use ZExt\Html\Exception;

/**
 * Html table row's abstraction
 * 
 * @package    Html
 * @subpackage Table
 * @author     Mike.Mirten
 * @version    1.0
 * 
 * @method TableRow    addElements(array $elements) Add an elements
 * @method TableCell[] getElements()                Get an elements
 * @method TableCell   getElement(string $name)     Get an element
 * @method TableRow    removeElement(string $name)  Remove an element
 */
class TableRow extends MultiElementsAbstract {
	
	/**
	 * Tag's name
	 *
	 * @var string 
	 */
	protected $_tag = 'tr';
	
	/**
	 * Add a cell to a row
	 * 
	 * @param  array | TableCell $element
	 * @param  string            $name
	 * @return TableRow
	 * @throws Exception
	 */
	public function addElement($cell, $name = null, $attrs = null) {
		if (is_scalar($cell)) {
			$cell = new TableCell($cell, $attrs);
		}
		else if (! $cell instanceof TableCell) {
			throw new Exception('Element must be an instance of the TableCell or a scalar');
		}
		
		return parent::addElement($cell, $name);
	}
	
}