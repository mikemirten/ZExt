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

use ZExt\Html\Navigation\Element,
    ZExt\Html\Navigation\Divider;

/**
 * Navigation based on the unordered list
 * 
 * @category   ZExt
 * @package    Html
 * @subpackage Navigation
 * @author     Mike.Mirten
 * @version    1.1
 */
class Navigation extends ListUnordered {
	
	const DIVIDER = '_divider_';
	
	/**
	 * Add an element to the navigation
	 * 
	 * @param  mixed  $element
	 * @param  string $title
	 * @param  string $name
	 * @return Navigation
	 */
	public function addElement($element, $title = null, $name = null, $attrs = null) {
		if (! $element instanceof Element &&
			! $element instanceof Divider) {
			
			if ($element === self::DIVIDER) {
				$element = new Divider(null, $attrs);
			} else {
				if ($title === null) {
					$title = $element;
				}
				
				$element = new Element($element, $title, $attrs);
			}
		}
		
		if ($name === null && $title !== null) {
			$name = $title;
		}
		
		return parent::addElement($element, $name);
	}
	
	/**
	 * Set an element to active
	 * 
	 * @param  string $name element's name
	 * @return Navigation
	 */
	public function setActive($name) {
		$element = $this->getElement($name);
		
		if ($element instanceof Element) {
			$element->setActive();
		}
		
		return $this;
	}
	
	/**
	 * Set an element to inactive
	 * 
	 * @param  string $name
	 * @return Navigation
	 */
	public function setInactive($name) {
		$element = $this->getElement($name);
		
		if ($element instanceof Element) {
			$element->setInactive();
		}
		
		return $this;
	}
	
	/**
	 * Set all element to inactive
	 * 
	 * @return Navigation
	 */
	public function setInactiveAll() {
		foreach ($this->getElements() as $element) {
			if ($element instanceof Element) {
				$element->setInactive();
			}
		}
		
		return $this;
	}
	
	/**
	 * Trigger the active element
	 * 
	 * @param  string $name
	 * @return Navigation
	 */
	public function triggerActive($name) {
		$this->setInactiveAll();
		$this->setActive($name);
		
		return $this;
	}
	
}