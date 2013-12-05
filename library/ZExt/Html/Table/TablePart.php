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

/**
 * Part of a table (thead / tbody / tfoot)
 * 
 * @package    Html
 * @subpackage Table
 * @author     Mike.Mirten
 * @version    1.0
 * 
 * @method Table      addElements(array $elements) Add an elements
 * @method TableRow[] getElements()                Get an elements
 * @method TableRow   getElement(string $name)     Get an element
 * @method Table      removeElement(string $name)  Remove an element
 */
class TablePart extends MultiElementsAbstract {
	
	/**
	 * Tag's name
	 *
	 * @var string 
	 */
	protected $_tag = 'tbody';
	
	/**
	 * Add a row to a table
	 * 
	 * @param  array | TableRow $element
	 * @param  string           $name
	 * @return Table
	 * @throws Exception
	 */
	public function addElement($row, $name = null, $attrs = null) {
		if (is_array($row)) {
			$row = new TableRow($row, $attrs);
		}
		else if (! $row instanceof TableRow) {
			throw new Exception('Element must be an instance of the TableRow or an array');
		}
		
		return parent::addElement($row, $name);
	}
	
}