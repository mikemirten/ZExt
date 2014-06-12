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
use ZExt\File\Linker;

/**
 * Style element trait
 * 
 * @category   ZExt
 * @package    Mvc
 * @subpackage ViewHelper
 * @author     Mike.Mirten
 * @version    1.0
 */
trait Style {
	
	/**
	 * Styles' links
	 *
	 * @var array
	 */
	protected $styleLinks = [];
	
	/**
	 * Join all the style files in one
	 *
	 * @var bool 
	 */
	protected $staticStylesJoint = false;
	
	/**
	 * Joint style filename
	 *
	 * @var string
	 */
	protected $jointStyleName = 'style.css';
	
	/**
	 * Join all the style files in one
	 * 
	 * @param  bool   $enable
	 * @param  string $filename
	 * @return Head
	 */
	public function setStyleFilesJoint($enable = true, $filename = null) {
		$this->staticStylesJoint = (bool) $enable;
		
		if ($filename !== null) {
			$this->jointStyleName = (string) $filename;
		}
		
		return $this;
	}
	
	/**
	 * Is the style files joining enabled ?
	 * 
	 * @return bool
	 */
	public function isStyleFilesJoint() {
		return $this->staticStylesJoint;
	}
	
	/**
	 * Set the style links
	 * 
	 * @param  string $links
	 * @return Head
	 */
	public function setStyleLinks(array $links) {
		$this->resetStyleLinks();
		
		foreach ($links as $href) {
			$this->appentStyleLink($href);
		}
		
		return $this;
	}
	
	/**
	 * Get the style links
	 * 
	 * @return array
	 */
	public function getStyleLinks() {
		return $this->styleLinks;
	}
	
	/**
	 * Appent the stylesheet link
	 * 
	 * @param  string $href
	 * @return Head
	 */
	public function appentStyleLink($href) {
		$this->styleLinks[] = (string) $href;
		
		return $this;
	}
	
	/**
	 * Prepend the stylesheet link
	 * 
	 * @param  string $href
	 * @return Head
	 */
	public function prependStyleLink($href) {
		array_unshift($this->styleLinks, (string) $href);
		
		return $this;
	}
	
	/**
	 * Reset the style links
	 * 
	 * @return Head
	 */
	public function resetStyleLinks() {
		$this->styleLinks = [];
		
		return $this;
	}
	
	/**
	 * Render the style
	 * 
	 * @return string
	 */
	protected function renderStyle() {
		if ($this->staticStylesJoint) {
			$basePath  = $this->getBaseStaticPath();
			$stylePath = $basePath . DIRECTORY_SEPARATOR . $this->jointStyleName;
		
			$linker = new Linker();
			$linker->setOutputPath($stylePath);
			
			foreach ($this->styleLinks as $link) {
				$linker->append($basePath . DIRECTORY_SEPARATOR . $link);
			}
			
			$linker->write();

			return $this->renderStyleLinks([$this->jointStyleName]);
		}
		
		return $this->renderStyleLinks($this->styleLinks);
	}
	
	/**
	 * Render the style links
	 * 
	 * @param  array $links
	 * @return string
	 */
	protected function renderStyleLinks(array $links) {
		$linkTag = new Tag('link');
		$linkTag->setClosed();
		
		$linkTag->rel  = 'stylesheet';
		$linkTag->type = 'text/css';
		
		$linksTags = [];
		
		foreach ($links as $href) {
			if ($href[0] === '/' || preg_match('~^[a-z]+:~', $href)) {
				$linkTag->href = $href;
			} else {
				if ($this->staticHash) {
					$href .= '?' . $this->getFileMeta($href)->hash;
				}
				
				$linkTag->href = $this->staticUrl . '/' . $href;
			}
			
			$linksTags[] = $linkTag->render();
		}
		
		return implode(PHP_EOL, $linksTags);
	}
	
}