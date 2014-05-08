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

namespace ZExt\Datagate\File;

use SeekableIterator,
    Countable;

/**
 * File's data iterator
 * 
 * @package    ZExt
 * @subpackage Datagate
 * @author     Mike.Mirten
 * @version    1.0
 */
class Iterator implements SeekableIterator, Countable {
	
	// Prefix for the unnamed data parts
	const UNNAMED_PREFIX = '_';
	
	/**
	 * Opened file
	 *
	 * @var source
	 */
	protected $file;
	
	/**
	 * Delimiter of parts of a data
	 *
	 * @var string | null
	 */
	protected $delimiter;
	
	/**
	 * Names of a parts of a data
	 *
	 * @var array | null
	 */
	protected $partsNames;
	
	/**
	 * Current position string
	 *
	 * @var string
	 */
	protected $string;
	
	/**
	 * Item's position pointer
	 *
	 * @var int
	 */
	protected $pointer = 0;
	
	/**
	 * Index data
	 *
	 * @var array
	 */
	protected $index;
	
	/**
	 * Constructor
	 * 
	 * @param source $file
	 * @param string $delimiter
	 * @param array  $partsNames
	 */
	public function __construct($file, $delimiter = null, array $partsNames = null) {
		$this->file       = $file;
		$this->delimiter  = $delimiter;
		$this->partsNames = $partsNames;
	}
	
	/**
	 * Seek a position of an item
	 * 
	 * @param int $position
	 */
	public function seek($position) {
		$index       = $this->getIndexData();
		$maxPosition = count($index) - 1;
		
		if ($position > $maxPosition) {
			$position = $maxPosition;
		}
		
		fseek($this->file, $index[$position]);
		$this->pointer = $position;
	}
	
	/**
	 * Get the current position's item
	 * 
	 * @return string | array
	 */
	public function current() {
		if ($this->string === null) {
			$this->string = fgets($this->file);
		}
		
		$string = rtrim($this->string, "\n\r");
		
		if ($this->delimiter === null) {
			return $string;
		} 
		
		$dataRaw = explode($this->delimiter, $string);
		$data    = [];

		if ($this->partsNames === null) {
			foreach ($dataRaw as $key => $part) {
				$data[self::UNNAMED_PREFIX . $key] = $part;
			}
			
			return $data;
		}
		
		foreach ($dataRaw as $key => $part) {
			if (isset($this->partsNames[$key])) {
				$data[$this->partsNames[$key]] = $part;
				continue;
			}
			
			$data[self::UNNAMED_PREFIX . $key] = $part;
		}

		return $data;
	}
	
	/**
	 * Get the current position's key
	 * 
	 * @return int
	 */
	public function key() {
		return $this->pointer;
	}
	
	/**
	 * Go to next item's position
	 */
	public function next() {
		$this->string = fgets($this->file);
		++ $this->pointer;
	}
	
	/**
	 * Go to start
	 */
	public function rewind() {
		if ($this->pointer === 0) {
			return;
		}
		
		fseek($this->file, 0);
		$this->pointer = 0;
	}
	
	/**
	 * Is the position valid ?
	 * 
	 * @return bool
	 */
	public function valid() {
		if ($this->string === null) {
			$this->string = fgets($this->file);
		}
		
		return $this->string === false ? false : true;
	}
	
	/**
	 * Get a number of an items
	 * 
	 * @return int
	 */
	public function count() {
		return count($this->getIndexData());
	}
	
	/**
	 * Set the index data about items' positions
	 * 
	 * @param array $data
	 */
	public function setIndexData(array $data) {
		$this->index = $data;
	}
	
	/**
	 * Get the index data about items' positions
	 * 
	 * @return array
	 */
	public function getIndexData() {
		if ($this->index === null) {
			$currentPointer = ftell($this->file);
			fseek($this->file, 0);
			
			$count       = 0;
			$this->index = [];
			
			while (fgets($this->file) !== false) {
				$this->index[++ $count] = ftell($this->file);
			}

			fseek($this->file, $currentPointer);
		}
		
		return $this->index;
	}
	
}