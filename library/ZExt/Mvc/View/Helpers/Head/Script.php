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
 * Script element trait
 * 
 * @category   ZExt
 * @package    Mvc
 * @subpackage ViewHelper
 * @author     Mike.Mirten
 * @version    1.0
 */
trait Script {
	
	/**
	 * Scripts' links
	 *
	 * @var array
	 */
	protected $scriptSources = [];
	
	/**
	 * Join all the script files in one
	 *
	 * @var bool 
	 */
	protected $staticScriptsJoint = false;
	
	/**
	 * Joint script filename
	 *
	 * @var string
	 */
	protected $jointScriptName = 'script.css';
	
	/**
	 * Join all the scripts files in one
	 * 
	 * @param  bool   $enable
	 * @param  string $filename
	 * @return Head
	 */
	public function setScriptFilesJoint($enable = true, $filename = null) {
		$this->staticScriptsJoint = (bool) $enable;
		
		if ($filename !== null) {
			$this->jointScriptName = (string) $filename;
		}
		
		return $this;
	}
	
	/**
	 * Is the script files joining enabled ?
	 * 
	 * @return bool
	 */
	public function isScriptFilesJoint() {
		return $this->staticScriptsJoint;
	}
	
	/**
	 * Set the script sources
	 * 
	 * @param  string $sources
	 * @return Head
	 */
	public function setScriptSources(array $sources) {
		$this->resetScriptSources();
		
		foreach ($sources as $src) {
			$this->appendScriptSource($src);
		}
		
		return $this;
	}
	
	/**
	 * Get the script sources
	 * 
	 * @return array
	 */
	public function getScriptSources() {
		return $this->scriptSources;
	}
	
	/**
	 * Appent the script source
	 * 
	 * @param  string $src
	 * @return Head
	 */
	public function appendScriptSource($src) {
		$this->scriptSources[] = (string) $src;
		
		return $this;
	}
	
	/**
	 * Prepend the script source
	 * 
	 * @param  string $src
	 * @return Head
	 */
	public function prependScriptSource($src) {
		array_unshift($this->scriptSources, (string) $src);
		
		return $this;
	}
	
	/**
	 * Reset the script sources
	 * 
	 * @return Head
	 */
	public function resetScriptSources() {
		$this->scriptSources = [];
		
		return $this;
	}
	
	/**
	 * Render the scripts
	 * 
	 * @return string
	 */
	protected function renderScripts() {
		if ($this->staticScriptsJoint) {
			$basePath   = $this->getBaseStaticPath();
			$scriptPath = $basePath . DIRECTORY_SEPARATOR . $this->jointScriptName;
		
			$linker = new Linker();
			$linker->setOutputPath($scriptPath);
			
			foreach ($this->scriptSources as $src) {
				$linker->append($basePath . DIRECTORY_SEPARATOR . $src);
			}
			
			$linker->write();

			return $this->renderScriptSources([$this->jointScriptName]);
		}
		
		return $this->renderScriptSources($this->scriptSources);
	}
	
	/**
	 * Render the style links
	 * 
	 * @param  array $sources
	 * @return string
	 */
	protected function renderScriptSources(array $sources) {
		$scriptTag = new Tag('script');
		
		$scriptTag->type = 'text/javascript';
		
		$scriptsTags = [];
		
		foreach ($sources as $src) {
			if ($src[0] === '/' || preg_match('~^[a-z]+:~', $src)) {
				$scriptTag->src = $src;
			} else {
				if ($this->staticHash) {
					$src .= '?' . $this->getFileMeta($src)->hash;
				}
				
				$scriptTag->src = $this->staticUrl . '/' . $src;
			}
			
			$scriptsTags[] = $scriptTag->render();
		}
		
		return implode(PHP_EOL, $scriptsTags);
	}
	
}