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

namespace ZExt\Xml;

use ZExt\Filesystem\FileInterface,
    ZExt\Filesystem\File;

use SimpleXMLElement;

/**
 * XML functions library
 * 
 * @category   ZExt
 * @package    Xml
 * @subpackage Xml
 * @author     Mike.Mirten
 * @version    1.0
 */
class Xml {
	
	/**
	 * Read and parse XML from file
	 * 
	 * @param  File | string $file
	 * @return Element
	 */
	static public function read($file) {
		if (! $file instanceof FileInterface) {
			$file = new File($file);
		}
		
		return static::parse($file->getContent());
	}
	
	/**
	 * Parse XML from string
	 * 
	 * @param  string $content
	 * @return Element
	 */
	static public function parse($content) {
		return new Element(new SimpleXMLElement($content));
	}
	
}