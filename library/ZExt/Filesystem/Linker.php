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

use ZExt\Filesystem\Exceptions\InvalidPath,
    ZExt\Filesystem\Exceptions\OperationError;

use SplQueue, IteratorAggregate, Countable, Exception;

/**
 * Files' linker
 * 
 * @category   ZExt
 * @package    File
 * @subpackage Linker
 * @author     Mike.Mirten
 * @version    1.0
 */
class Linker implements IteratorAggregate, Countable {
	
	/**
	 * List of input files' pathes
	 *
	 * @var SplQueue
	 */
	protected $inputFiles;
	
	/**
	 * Outout file path
	 *
	 * @var string
	 */
	protected $outputFile;
	
	/**
	 * Delimiter
	 *
	 * @var string
	 */
	protected $delimiter = PHP_EOL;
	
	/**
	 * Additional file info
	 *
	 * @var bool
	 */
	protected $fileInfo = false;
	
	/**
	 * File info template
	 *
	 * @var string
	 */
	protected $fileInfoTemplate = '/* %s */';
	
	/**
	 * Reassemble by modification time
	 *
	 * @var bool
	 */
	protected $reassembleByMTime = true;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->reset();
	}
	
	/**
	 * Set the path to the output file
	 * 
	 * @param  string $path
	 * @return Linker
	 * @throws InvalidPath
	 */
	public function setOutputPath($path) {
		$dir = pathinfo($path, PATHINFO_DIRNAME);
		$dir = $this->normalizePath($dir);
		
		if (! is_dir($dir)) {
			throw new InvalidPath('Invalid directory: "' . $dir . '"');
		}
		
		if (! is_writable($dir)) {
			throw new InvalidPath('The directory: "' . $dir . '" is unwritable');
		}
		
		if (is_file($path) && ! is_writable($path)) {
			throw new InvalidPath('The File "' . $path . '" exists. but unwritable');
		}
		
		$this->outputFile = $dir . DIRECTORY_SEPARATOR . pathinfo($path, PATHINFO_BASENAME);
		
		return $this;
	}

	/**
	 * Append the file
	 * 
	 * @param  string $path
	 * @return Linker
	 */
	public function append($path) {
		$path = $this->normalizePath($path);
		
		$this->inputFiles->push($path);
		
		return $this;
	}
	
	/**
	 * Prepend the file
	 * 
	 * @param  string $path
	 * @return Linker
	 */
	public function prepend($path) {
		$path = $this->normalizePath($path);
		
		$this->inputFiles->unshift($path);
		
		return $this;
	}
	
	/**
	 * Reset the linker (remove all the files)
	 * 
	 * @return Linker
	 */
	public function reset() {
		$this->inputFiles = new SplQueue();
		
		return $this;
	}
	
	/**
	 * Assemble the files chain
	 * 
	 * @return string
	 */
	public function assemble() {
		if ($this->inputFiles->isEmpty()) {
			throw new OperationError('Nothing to assemble, you should add at least one item');
		}
		
		$result = '';
		
		foreach ($this->inputFiles as $path) {
			$content = file_get_contents($path);
			
			if ($content === false) {
				throw new OperationError('Unable to read the file: "' . $path . '"');
			}
			
			if ($this->fileInfo) {
				$result .= sprintf($this->fileInfoTemplate, $path);
				$result .= $this->delimiter;
			}
			
			$result .= $content;
			$result .= $this->delimiter;
		}
		
		return $result;
	}
	
	/**
	 * Assemble and write the file
	 * 
	 * @param  bool $forceAssemble
	 * @throws OperationError
	 */
	public function write($forceAssemble = false) {
		if ($this->outputFile === null) {
			throw new OperationError('Path to the output file must be set first');
		}
		
		if (! $forceAssemble && $this->reassembleByMTime && ! $this->isOutputExpired()) {
			return;
		}
		
		$result = file_put_contents($this->outputFile, $this->assemble());
		
		if ($result === false) {
			throw new OperationError('Unable to write the output file: "' . $this->outputFile . '"');
		}
	}
	
	/**
	 * Is output file expired ?
	 * 
	 * @return bool
	 * @throws OperationError
	 */
	protected function isOutputExpired() {
		if ($this->inputFiles->isEmpty()) {
			throw new OperationError('Nothing to assemble, you should add at least one item');
		}
		
		if (! is_file($this->outputFile)) {
			return true;
		}
		
		$outputMtime = $this->getFileMTime($this->outputFile);
		
		foreach ($this->inputFiles as $path) {
			if ($outputMtime < $this->getFileMTime($path)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Get the file's modification time
	 * 
	 * @param  string $path
	 * @return int
	 * @throws OperationError
	 */
	protected function getFileMTime($path) {
		$mtime = filemtime($path);
			
		if ($mtime === false) {
			throw new OperationError('Unable to determine modification time of the file: "' . $path . '"');
		}
		
		return $mtime;
	}
	
	/**
	 * Set additional file info
	 * 
	 * @param  bool   $flag     Enable/Disable
	 * @param  string $template Formatted print template
	 * @return Linker
	 */
	public function setFileInfo($flag = true, $template = null) {
		$this->fileInfo         = (bool)   $flag;
		
		if ($template !== null) {
			$this->fileInfoTemplate = (string) $template;
		}
		
		return $this;
	}
	
	/**
	 * Reassemble only if source files modification time changed
	 * 
	 * @param  bool $flag
	 * @return Linker
	 */
	public function setReassembleByMTime($flag = true) {
		$this->reassembleByMTime = (bool) $flag;
		
		return $this;
	}
	
	/**
	 * Normalize the path
	 * 
	 * @param  string $pathRaw
	 * @return string
	 * @throws InvalidPath
	 */
	protected function normalizePath($pathRaw) {
		$path = realpath($pathRaw);
		
		if ($path === false) {
			throw new InvalidPath('Invalid path: "' . $pathRaw . '"');
		}
		
		return $path;
	}
	
	/**
	 * Is the linker empty ?
	 * 
	 * @return bool
	 */
	public function isEmpty() {
		return $this->inputFiles->isEmpty();
	}
	
	/**
	 * Get the files iterator
	 * 
	 * @return \Traversable
	 */
	public function getIterator() {
		return $this->inputFiles;
	}
	
	/**
	 * Count the files
	 * 
	 * @return int
	 */
	public function count() {
		return $this->inputFiles->count();
	}
	
	/**
	 * Assemble
	 * 
	 * @return string
	 */
	public function __toString() {
		try {
			return $this->assemble();
		} catch (Exception $e) {
			return 'Error occurred: ' . $e->getMessage();
		}
	}
	
}