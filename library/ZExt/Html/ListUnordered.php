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

/**
 * Html list's abstraction
 * 
 * @package    Html
 * @subpackage List
 * @author     Mike.Mirten
 * @version    1.1
 * 
 * @method ListUnordered addElements(array $elements) Add an elements
 * @method ListElement[] getElements()                Get an elements
 * @method ListElement   getElement(string $name)     Get an element
 * @method ListUnordered removeElement(string $name)  Remove an element
 */
class ListUnordered extends MultiElementsAbstract {
	
	/**
	 * Tag's name
	 *
	 * @var string 
	 */
	protected $_tag = 'ul';
	
	/**
	 * Add an element to a list
	 * 
	 * @param  mixed  $element
	 * @param  string $name
	 * @return ListUnordered
	 */
	public function addElement($element, $name = null, $attrs = null) {
		switch (true) {
			case is_array($element):
				$newElement = new static($element);
				break;

			case $element instanceof self:
			case $element instanceof ListElement:
				$newElement = $element;
				break;

			default:
				$newElement = new ListElement($element, $attrs);
		}
		
		return parent::addElement($newElement, $name);
	}
	
}