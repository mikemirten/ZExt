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
 * Anchor tag abstraction
 * 
 * @package    Html
 * @subpackage Tag
 * @author     Mike.Mirten
 * @version    1.0
 */
class Anchor extends Tag {
	
	const ATTR_HREF = 'href';
	
	/**
	 * Tag's name
	 *
	 * @var string 
	 */
	protected $_tag = 'a';
	
	/**
	 * Constructor
	 * 
	 * @param string | array $link
	 * @param string | Tag   $html
	 * @param string | array $attrs
	 */
	public function __construct($link = null, $html = null, $attrs = null) {
		if ($link !== null) {
			$this->setLink($link);
		}
		
		parent::__construct(null, $html, $attrs);
	}
	
	/**
	 * Set the link ("href" attribute) of the anchor
	 * 
	 * @param  string $link
	 * @return Anchor
	 */
	public function setLink($link) {
		$this->setAttr(self::ATTR_HREF, $link);
		
		return $this;
	}
	
	/**
	 * Get the link ("href" attribute) of the anchor
	 * 
	 * @param string $link
	 */
	public function getLink() {
		return $this->getAttr(self::ATTR_HREF);
	}
	
	/**
	 * Render an anchor
	 * 
	 * @param  string $html
	 * @return string
	 */
	public function render($html = null) {
		if ($html === null && $this->getHtml() === '') {
			$html = $this->getLink();
		}
		
		return parent::render($html);
	}
	
}