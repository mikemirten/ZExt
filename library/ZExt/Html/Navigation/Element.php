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

namespace ZExt\Html\Navigation;

use ZExt\Html\ListElement,
    ZExt\Html\Anchor;

/**
 * Navigation element
 * 
 * @category   ZExt
 * @package    Html
 * @subpackage Navigation
 * @author     Mike.Mirten
 * @version    1.0
 */
class Element extends ListElement {
	
	const ATTR_ACTIVE = '_active_';
	const ATTR_ANCHOR = '_anchor_';
	
	const CLASS_ACTIVE = 'active';
	
	/**
	 * Anchor tag of the element
	 *
	 * @var Anchor
	 */
	protected $_anchor;
	
	/**
	 * Is element active
	 *
	 * @var bool
	 */
	protected $_active = false;
	
	/**
	 * Constructor
	 * 
	 * @param Anchor | array | string $link
	 * @param string                  $title
	 * @param string | array          $attrs
	 */
	public function __construct($link = null, $title = null, $attrs = null) {
		if (is_string($link)) {
			$this->getAnchor()->setLink($link);
		} else if ($link instanceof Anchor) {
			$this->setAnchor($link);
		}
		
		if ($title !== null) {
			$this->setTitle($title);
		}
		
		if (is_array($attrs)) {
			if (isset($attrs[self::ATTR_ACTIVE]) 
			&&  $attrs[self::ATTR_ACTIVE] === true) {
				$this->setActive();
				unset($attrs[self::ATTR_ACTIVE]);
			}
			
			if (isset($attrs[self::ATTR_ANCHOR])) {
				$link->setAttrs($attrs[self::ATTR_ANCHOR]);
				unset($attrs[self::ATTR_ANCHOR]);
			}
		}
		
		parent::__construct(null, $attrs);
	}
	
	/**
	 * Get the title
	 * 
	 * @return string
	 */
	public function getTitle() {
		return $this->getAnchor()->getHtml();
	}
	
	/**
	 * Set the title
	 * 
	 * @param  string $title
	 * @return Element
	 */
	public function setTitle($title) {
		$this->getAnchor()->setHtml($title);
		
		return $this;
	}
	
	/**
	 * Get an anchor of the element
	 * 
	 * @return Anchor
	 */
	public function getAnchor() {
		if ($this->_anchor === null) {
			$this->_anchor = new Anchor();
		}
		
		return $this->_anchor;
	}
	
	/**
	 * Set an anchor of the element
	 * 
	 * @param  Anchor
	 * @return Element
	 */
	public function setAnchor(Anchor $anchor) {
		$this->_anchor = $anchor;
		
		return $this;
	}
	
	/**
	 * Set the element active
	 * 
	 * @return Element
	 */
	public function setActive() {
		if (! $this->_active) {
			$this->addClass(self::CLASS_ACTIVE);
			$this->_active = true;
		}
		
		return $this;
	}
	
	/**
	 * Set the element inactive
	 * 
	 * @return Element
	 */
	public function setInactive() {
		if ($this->_active) {
			$this->removeClass(self::CLASS_ACTIVE);
			$this->_active = false;
		}
		
		return $this;
	}
	
	/**
	 * Check is the element active
	 * 
	 * @return bool
	 */
	public function isActive() {
		return $this->_active;
	}
	
	/**
	 * Render the navigation element
	 * 
	 * @param  string $html
	 * @return string
	 */
	public function render($html = null) {
		$link = $this->getAnchor()->render();
		
		if ($html !== null) {
			$link .= $this->getSeparator();
			$link .= $html;
		}
		
		return parent::render($link);
	}
	
}