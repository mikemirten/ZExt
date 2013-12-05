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
 * Html table colgroup's abstraction
 * 
 * @package    Html
 * @subpackage Table
 * @author     Mike.Mirten
 * @version    1.0
 */
class TableColgroup extends MultiElementsAbstract {
	
	const ATTR_WIDTH = 'width';
	
	/**
	 * Tag's name
	 *
	 * @var string 
	 */
	protected $_tag = 'colgroup';
	
	/**
	 * Add a cell to a row
	 * 
	 * @param  array | TableCol $element Instance of the TableCol or an array of attributes
	 * @param  string           $name
	 * @return TableRow
	 * @throws Exception
	 */
	public function addElement($col, $name = null) {
		// Width
		if (is_int($col)) {
			$col = new TableCol([self::ATTR_WIDTH => $col . '%']);
		}
		// Attributes or class
		else if (is_array($col) || is_string($col)) {
			$col = new TableCol($col);
		}
		// Instance
		else if (! $col instanceof TableCol) {
			throw new Exception('Element must be an instance of the TableCol or a array or a string');
		}
		
		return parent::addElement($col, $name);
	}
	
}