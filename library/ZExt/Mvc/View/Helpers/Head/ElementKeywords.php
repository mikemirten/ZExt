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
 * Keywords element
 * 
 * @category   ZExt
 * @package    Mvc
 * @subpackage ViewHelper
 * @author     Mike.Mirten
 * @version    1.0
 */
class ElementKeywords implements ElementInterface {
	
	/**
	 * Meta keywords
	 *
	 * @var array
	 */
	protected $keywords = [];
	
	/**
	 * Set the keywords (owerrides the current keywords)
	 * 
	 * @param  array | Traversable $keywords
	 * @return Head
	 */
	public function setKeywords($keywords) {
		$this->resetKeywords();
		$this->addKeywords($keywords);
		
		return $this;
	}
	
	/**
	 * Add many of the keywords
	 * 
	 * @param  array | Traversable | string $keywords
	 * @return Head
	 */
	public function addKeywords($keywords) {
		if (is_string($keywords)) {
			$keywords = explode(',', $keywords);
		}
		
		foreach ($keywords as $keyword) {
			$this->addKeyword($keyword);
		}
		
		return $this;
	}
	
	/**
	 * Add the keyword
	 * 
	 * @param  string $keyword
	 * @return Head
	 */
	public function addKeyword($keyword) {
		$this->keywords[] = trim($keyword);
		
		return $this;
	}
	
	/**
	 * Reset the keywords
	 * 
	 * @return Head
	 */
	public function resetKeywords() {
		$this->keywords = [];
	}
	
	/**
	 * Get the keywords
	 * 
	 * @return string
	 */
	public function getKeywords() {
		return implode(',', $this->keywords);
	}
	
	/**
	 * Get the raw keywords content
	 * 
	 * @return array
	 */
	public function getKeywordsRaw() {
		return $this->keywords;
	}
	
	/**
	 * Is the keywords empty ?
	 * 
	 * @return bool
	 */
	public function isEmpty() {
		return empty($this->keywords);
	}

		/**
	 * Assemble the keywords tag
	 * 
	 * @param  array $keywords
	 * @return string
	 */
	public function assemble() {
		$keysTag = new Tag('meta');
		$keysTag->setClosed();

		$keysTag->name    = 'keywords';
		$keysTag->content = $this->getKeywords();

		return $keysTag->render();
	}
	
	/**
	 * Default action of the element
	 * 
	 * @param array | Traversable | string $value
	 */
	public function defaultAction($value) {
		$this->addKeywords($value);
	}
	
}