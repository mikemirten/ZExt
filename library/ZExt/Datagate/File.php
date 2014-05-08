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

namespace ZExt\Datagate;

use ZExt\Datagate\File\Iterator as FileIterator;
use ZExt\Model\ModelInterface;

use ZExt\Datagate\Exceptions\OperationError;
use ZExt\Datagate\Exceptions\NoPath;

/**
 * File datagate abstraction
 * 
 * @package    ZExt
 * @subpackage Datagate
 * @author     Mike.Mirten
 * @version    1.1
 */
class File extends DatagateAbstract {
	
	/**
	 * Path to a file
	 *
	 * @var string
	 */
	protected $path;
	
	/**
	 * Properties' delimiter
	 *
	 * @var string
	 */
	protected $delimiter;
	
	/**
	 * Names for a parts of a row (works only with delimiter)
	 *
	 * @var array 
	 */
	protected $partsNames;
	
	/**
	 * Opened file
	 *
	 * @var resource
	 */
	private $file;
	
	/**
	 * Get data as iterator
	 * 
	 * @return \Iterator
	 */
	public function getIterator() {
		$fileIterator = new FileIterator(
			$this->getFile(),
			$this->delimiter,
			$this->partsNames
		);
		
		if ($this->delimiter === null) {
			return $fileIterator;
		}
		
		return $this->createIterator($fileIterator);
	}
	
	/**
	 * Get an opened file
	 * 
	 * @return resource
	 * @throws NoPath
	 */
	protected function getFile() {
		if ($this->file === null) {
			$path = $this->getFilePath();

			if (! is_file($path)) {
				throw new NoPath('Unable to locate the file: ' . $path);
			}

			$file = fopen($path, 'r');

			if ($file === false) {
				throw new NoPath('Unable to open the file: ' . $path);
			}
		}
		
		return $file;
	}
	
	/**
	 * Set a path to a file
	 * 
	 * @param  string $path
	 * @return File
	 */
	public function setFilePath($path) {
		$path = realpath($path);
		
		if ($path === false) {
			throw new NoPath('Invalid path: ' . $path);
		}
		
		$this->path = $path;
		
		return $this;
	}
	
	/**
	 * Get a path to a file
	 * 
	 * @return string
	 * @throws NoPath
	 */
	public function getFilePath() {
		if ($this->path === null) {
			throw new NoPath('Path to a file was not been specified');
		}
		
		// Absolute path
		if ($this->path[0] === DIRECTORY_SEPARATOR) {
			return $this->path;
		}
		// Relative path
		return $this->getIntrospectiveData()->dirname . DIRECTORY_SEPARATOR . $this->path;
	}
	
	/**
	 * Set a data delimiter
	 * 
	 * @param  string $delimiter
	 * @return File
	 */
	public function setDelimiter($delimiter) {
		$this->delimiter = (string) $delimiter;
		
		return $this;
	}
	
	/**
	 * Get a data delimiter
	 * 
	 * @return string | null
	 */
	public function getDelimiter() {
		return $this->delimiter;
	}
	
	/**
	 * Set a names for a parts of a row
	 * 
	 * @param  array $names
	 * @return Datagate\File
	 */
	public function setPartsNames(array $names) {
		$this->partsNames = $names;
		
		return $this;
	}
	
	/**
	 * Get a names of a parts of a row
	 * 
	 * @return array | null
	 */
	public function getPartsNames() {
		return $this->partsNames;
	}

	/**
	 * Find all records of a data
	 * 
	 * @param  mixed $criteria Query criteria
	 * @return Collection | Iterator
	 */
	public function find($criteria = null) {
		if ($criteria !== null) {
			trigger_error('Attempts to set criteria are vain for the File datagate');
		}
		
		$fileIterator = new FileIterator(
			$this->getFile(),
			$this->delimiter,
			$this->partsNames
		);
		
		return $this->createResultset($fileIterator);
	}

	/**
	 * Find a record or a dataset by the primary id or an array of the ids
	 * 
	 * @param  mixed $id The primary key or an array of the primary keys
	 * @return ModelInterface | Collection | Iterator
	 */
	public function findByPrimaryId($id) {
		throw new OperationError('Impossible to find the model by a primary ID for the File datagate');
	}

	/**
	 * Find a first record
	 * 
	 * @param  mixed $criteria Query criteria
	 * @return ModelInterface
	 */
	public function findFirst($criteria = null) {
		if ($criteria !== null) {
			trigger_error('Attempts to set criteria are vain for the File datagate');
		}
		
		$fileIterator = new FileIterator(
			$this->getFile(),
			$this->delimiter,
			$this->partsNames
		);
		
		return $this->createResult($fileIterator->current());
	}

	/**
	 * Remove the record or the many of records by the model or the collection of the models
	 * 
	 * @param  ModelInterface $model
	 * @return bool True if succeeded
	 */
	public function remove(ModelInterface $model) {
		throw new OperationError('Impossible to remove the model for the File datagate');
	}

	/**
	 * Save the model or the collection of the models
	 * 
	 * @param  ModelInterface $model
	 * @return bool True if succeeded
	 */
	public function save(ModelInterface $model) {
		throw new OperationError('Impossible to save the model for the File datagate');
	}
	
}