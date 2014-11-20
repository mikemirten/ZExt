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

namespace ZExt\Filesystem;

/**
 * File abstraction
 * 
 * @category   ZExt
 * @package    File
 * @subpackage File
 * @author     Mike.Mirten
 * @version    1.0
 */
class File implements FileInterface {
	
	/**
	 * Path to file
	 *
	 * @var string 
	 */
	protected $path;
	
	/**
	 * Processed by realpath() path to file
	 *
	 * @var string 
	 */
	protected $realpath;
	
	/**
	 * Constructor
	 * 
	 * @param string $path
	 */
	public function __construct($path) {
		$this->path = $path;
	}
	
	/**
	 * Get content of file
	 * 
	 * @return string
	 * @throws Exceptions\InvalidPath
	 */
	public function getContent() {
		$content = file_get_contents($this->getRealpath());
		
		if ($content === false) {
			throw new Exceptions\InvalidPath('File "' . $path . '" is unreadable', null, null, $this->path);
		}
		
		return $content;
	}
	
	/**
	 * Get full path to file
	 * 
	 * @return string
	 * @throws Exceptions\InvalidPath
	 */
	public function getRealpath() {
		if ($this->realpath === null) {
			$this->realpath = realpath($this->path);
		
			if ($this->realpath === false) {
				throw new Exceptions\InvalidPath('Path "' . $this->path . '" doesn\'t exists or inaccsessible', null, null, $this->path);
			}
		}
		
		return $this->realpath;
	}
	
	/**
	 * Get extension of file
	 * 
	 * @return string | null
	 */
	public function getExtension() {
		$dotPos = strrpos($this->path, '.');
		
		if ($dotPos === false) {
			return;
		}
		
		return substr($this->path, $dotPos + 1);
	}
	
}