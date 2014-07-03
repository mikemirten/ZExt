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

namespace ZExt\Mvc\View\Helpers\Head;

use ZExt\Html\Tag;

/**
 * Title element
 * 
 * @category   ZExt
 * @package    Mvc
 * @subpackage ViewHelper
 * @author     Mike.Mirten
 * @version    1.0
 */
class ElementTitle implements ElementInterface {
	
	/**
	 * Title content
	 * 
	 * @var array
	 */
	protected $titleContent = [];
	
	/**
	 * Title delimiter
	 *
	 * @var string
	 */
	protected $titleDelimiter = ' ';
	
	/**
	 * Set the title (owerrides the current title)
	 * 
	 * @param  string $title
	 * @return Head
	 */
	public function setTitle($title) {
		$this->resetTitle();
		$this->appendTitle($title);
		
		return $this;
	}
	
	/**
	 * Append the part to the title
	 * 
	 * @param  string $title
	 * @return Head
	 */
	public function appendTitle($title) {
		$this->titleContent[] = trim($title);
		
		return $this;
	}
	
	/**
	 * Prepend the part to the title
	 * 
	 * @param  string $title
	 * @return Head
	 */
	public function prependTitle($title) {
		array_unshift($this->titleContent, trim($title));
		
		return $this;
	}
	
	/**
	 * Reset the title content
	 * 
	 * @return Head
	 */
	public function resetTitle() {
		$this->titleContent = [];
		
		return $this;
	}
	
	/**
	 * Set the title delimiter
	 * 
	 * @param  string $delimiter
	 * @return Head
	 */
	public function setTitleDelimiter($delimiter) {
		$this->titleDelimiter = (string) $delimiter;
		
		return $this;
	}
	
	/**
	 * Get the title delimiter
	 * 
	 * @return string
	 */
	public function getTitleDelimiter() {
		return $this->titleDelimiter;
	}
	
	/**
	 * Get the title
	 * 
	 * @return string
	 */
	public function getTitle() {
		return implode($this->titleDelimiter, $this->titleContent);
	}
	
	/**
	 * Get the raw title content
	 * 
	 * @return array
	 */
	public function getTitleRaw() {
		return $this->titleContent;
	}
	
	/**
	 * Is the title empty ?
	 * 
	 * @return bool
	 */
	public function isEmpty() {
		return empty($this->titleContent);
	}
	
	/**
	 * Assemble the title
	 * 
	 * @return string
	 */
	public function assemble() {
		$titleTag = new Tag('title', $this->getTitle());
		
		return $titleTag->render();
	}
	
	/**
	 * Default action of the element
	 * 
	 * @param mixed $value
	 */
	public function defaultAction($value) {
		$this->appendTitle($value);
	}
	
}