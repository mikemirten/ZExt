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

namespace ZExt\Mvc\View\Helpers;

use ZExt\Components\OptionsTrait;
use ZExt\Helper\HelperAbstract;
use ZExt\Html\Tag;

use Exception;

/**
 * Head elements helper
 * 
 * @category   ZExt
 * @package    Mvc
 * @subpackage ViewHelper
 * @author     Mike.Mirten
 * @version    1.0
 */
class Head extends HelperAbstract {
	
	use OptionsTrait;
	
	/**
	 * Meta description
	 *
	 * @var string
	 */
	protected $description;
	
	/**
	 * Meta keywords
	 *
	 * @var 
	 */
	protected $keywords = [];
	
	/**
	 * Meta charset
	 *
	 * @var string
	 */
	protected $encoding;
	
	/**
	 * Title content
	 * 
	 * @var string
	 */
	protected $titleContent = [];
	
	/**
	 * Title delimiter
	 *
	 * @var string
	 */
	protected $titleDelimiter = ' ';
	
	/**
	 * The helper "main"
	 * 
	 * @return Meta
	 */
	public function head($options = null) {
		if ($options !== null) {
			$this->setOptions($options, false, false);
		}
		
		return $this;
	}
	
	/**
	 * Set the description
	 * 
	 * @param  string $description
	 * @return Meta
	 */
	public function setDescription($description) {
		$this->description = trim($description);
		
		return $this;
	}
	
	/**
	 * Get the description
	 * 
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Set the keywords (owerrides the current keywords)
	 * 
	 * @param  array | Traversable $keywords
	 * @return Meta
	 */
	public function setKeywords($keywords) {
		$this->resetKeywords();
		$this->addKeywords($keywords);
		
		return $this;
	}
	
	/**
	 * Add many of the keywords
	 * 
	 * @param  array | Traversable $keywords
	 * @return Meta
	 */
	public function addKeywords($keywords) {
		foreach ($keywords as $keyword) {
			$this->addKeyword($keyword);
		}
		
		return $this;
	}
	
	/**
	 * Add the keyword
	 * 
	 * @param  string $keyword
	 * @return Meta
	 */
	public function addKeyword($keyword) {
		$this->keywords[] = trim($keyword);
		
		return $this;
	}
	
	/**
	 * Reset the keywords
	 * 
	 * @return Meta
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
	 * Set the encoding
	 * 
	 * @param  string $encoding
	 * @return Meta
	 */
	public function setEncoding($encoding) {
		$this->encoding = trim($encoding);
		
		return $this;
	}
	
	/**
	 * Get the encoding
	 * 
	 * @return string
	 */
	public function getEncoding() {
		return $this->encoding;
	}
	
	/**
	 * Set the title (owerrides the current title)
	 * 
	 * @param  string $title
	 * @return Meta
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
	 * @return Meta
	 */
	public function appendTitle($title) {
		$this->titleContent[] = trim($title);
		
		return $this;
	}
	
	/**
	 * Prepend the part to the title
	 * 
	 * @param  string $title
	 * @return Meta
	 */
	public function prependTitle($title) {
		array_unshift($this->titleContent, trim($title));
		
		return $this;
	}
	
	/**
	 * 
	 * @return Meta
	 */
	public function resetTitle() {
		$this->titleContent = [];
		
		return $this;
	}
	
	/**
	 * Set the title delimiter
	 * 
	 * @param  string $delimiter
	 * @return Meta
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
	 * Render the meta tags
	 * 
	 * @return string
	 */
	public function render() {
		$parts = [];
		
		// Encoding
		if ($this->encoding !== null) {
			$parts[] = $this->renderEncoding($this->encoding);
		}
		
		// Description
		if ($this->description !== null) {
			$parts[] = $this->renderDescription($this->description);
		}
		
		// Keywords
		if (! empty($this->keywords)) {
			$parts[] = $this->renderKeywords($this->keywords);
		}
		
		// Title
		if (! empty($this->titleContent)) {
			$parts[] = $this->renderTitle($this->titleDelimiter, $this->titleContent);
		}
		
		return implode(PHP_EOL, $parts);
	}
	
	/**
	 * Render the encoding tag
	 * 
	 * @param  string $encoding
	 * @return string
	 */
	protected function renderEncoding($encoding) {
		$encTag = new Tag('meta');
		$encTag->setClosed();
		
		$encTag->charset = $encoding;
		
		return $encTag->render();
	}
	
	/**
	 * Render the keywords tag
	 * 
	 * @param  array $keywords
	 * @return string
	 */
	protected function renderKeywords(array $keywords) {
		$keysTag = new Tag('meta');
		$keysTag->setClosed();

		$keysTag->name    = 'keywords';
		$keysTag->content = implode(',', $keywords);

		return $keysTag->render();
	}
	
	/**
	 * Render the description tag
	 * 
	 * @param  string $description
	 * @return string
	 */
	protected function renderDescription($description) {
		$descTag = new Tag('meta');
		$descTag->setClosed();

		$descTag->name    = 'description';
		$descTag->content = $description;

		return $descTag->render();
	}
	
	/**
	 * Render the title
	 * 
	 * @param  string $delimiter
	 * @param  array  $content
	 * @return string
	 */
	protected function renderTitle($delimiter, array $content) {
		$title    = implode($delimiter, $content);
		$titleTag = new Tag('title', $title);
		
		return $titleTag->render();
	}
	
	/**
	 * Render the meta tags
	 * 
	 * @return string
	 */
	public function __toString() {
		// toString() must not throw an exception
		try {
			return $this->render();
		} catch (Exception $e) {
			return '<!-- Error has occurred -->';
		}
	}
	
}