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

namespace ZExt\Cache\Backend;

use Phalcon\Cache\BackendInterface as PhalconBackendInterface;
use Phalcon\Cache\Exception        as PhalconCacheException;

use ZExt\Cache\Backend\Exceptions\NoBackend;
use ZExt\Cache\Backend\Exceptions\OperationFailed;

use ZExt\Topology\Descriptor;

/**
 * Phalcon cache backend adapter
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Backend
 * @author     Mike.Mirten
 * @version    1.0.1beta
 */
class Phalcon extends BackendAbstract {
	
	/**
	 * Phalcon cache backend instance
	 *
	 * @var PhalconBackendInterface
	 */
	private $phalconBackend;
	
	/**
	 * Namespace for the data ID's
	 *
	 * @var string
	 */
	protected $namespace;
	
	/**
	 * Throw an operation exceptions
	 *
	 * @var bool 
	 */
	protected $operationsExceptions = true;
	
	/**
	 * Constructor
	 * 
	 * Parameters:
	 * param name          | datatype         | default | description
	 * ===========================================================================================
	 * namespace           | string           | null    | Namespace of an IDs
	 * operationExceptions | bool             | true    | Throw the exceptions by operation errors
	 * backend             | BackendInterface | null    | Configured Phalcon backend instance
	 * 
	 * @param PhalconBackendInterface $backend
	 * @param array | Traversable     $options
	 */
	public function __construct($backend = null, $options = null) {
		if ($backend !== null) {
			if ($backend instanceof PhalconBackendInterface) {
				$this->setBackend($backend);
			} else {
				$options = $backend;
			}
		}
		
		if ($options !== null) {
			$this->setOptions($options);
		}
	}
	
	/**
	 * Fetch the data from the cache
	 * 
	 * @param  string | array $id
	 * @return mixed
	 * @throws OperationFailed
	 */
	public function get($id) {
		try {
			$result = $this->getBackend()->get($this->prepareId($id));
		}
		catch (PhalconCacheException $exception) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Fetching of the data from the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return;
			}
		}
		
		return $result;
	}
	
	/**
	 * Fetch the many of the data from the cache
	 * 
	 * @param  array $id
	 * @return array
	 * @throws OperationFailed
	 */
	public function getMany(array $id) {
		$idPrepared = array_map([$this, 'prepareId'], $id);
		$idsMap     = array_combine($idPrepared, $id);
		$backend    = $this->getBackend();
		
		$results = [];
		
		foreach ($idPrepared as $partId) {
			try {
				$result = $backend->get($partId);
			}
			catch (PhalconCacheException $exception) {
				if ($this->operationsExceptions) {
					throw new OperationFailed('Fetching of the data from the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
				} else {
					return [];
				}
			}
			
			if ($result !== null) {
				$results[$idsMap[$partId]] = $result;
			}
		}
		
		return $results;
	}
	
	/**
	 * Store the data into the cache
	 * 
	 * @param  string $id       ID of the stored data
	 * @param  mixed  $data     Stored data
	 * @param  int    $lifetime Lifetime in seconds
	 * @return bool
	 * @throws OperationFailed
	 */
	public function set($id, $data, $lifetime = 0) {
		try {
			$result = $this->getBackend()->save($this->prepareId($id), $data, $lifetime);
		}
		catch (PhalconCacheException $exception) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Inserting of the data into the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		if ($result === false) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Inserting of the data into the cache failed, ID: "' . $id . '"');
			} else {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Store the many of the date
	 * 
	 * @param  array $data
	 * @param  int   $lifetime
	 * @return bool
	 * @throws OperationFailed
	 */
	public function setMany(array $data, $lifetime = 0) {
		$backend = $this->getBackend();
		
		$ids  = array_map([$this, 'prepareId'], array_keys($data));
		$data = array_combine($ids, array_values($data));
		
		foreach ($data as $id => $dataPart) {
			try {
				$result = $backend->save($id, $dataPart, $lifetime);
			}
			catch (PhalconCacheException $exception) {
				if ($this->operationsExceptions) {
					throw new OperationFailed('Inserting of the data into the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
				} else {
					return false;
				}
			}
			
			if ($result === false) {
				if ($this->operationsExceptions) {
					throw new OperationFailed('Inserting of the data into the cache failed, ID: "' . $id . '"');
				} else {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Check whether the data exists in the cache
	 * 
	 * @param  string $id
	 * @return bool
	 * @throws OperationFailed
	 */
	public function has($id) {
		try {
			$result = $this->getBackend()->exists($this->prepareId($id));
		}
		catch (PhalconCacheException $exception) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Checking of the data exiting in the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		return $result;
	}
	
	/**
	 * Remove the data from the cache
	 * 
	 * @param  string $id
	 * @return bool
	 * @throws OperationFailed
	 */
	public function remove($id) {
		try {
			return $this->getBackend()->delete($this->prepareId($id));
		}
		catch (PhalconCacheException $exception) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Removing of the data from the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
	}
	
	/**
	 * Remove the many the data from the cache
	 * 
	 * @param  array $id
	 * @return bool
	 * @throws OperationFailed
	 */
	public function removeMany(array $ids) {
		$backend = $this->getBackend();
		$ids     = array_map([$this, 'prepareId'], $ids);
		
		$result = true;
		
		try {
			foreach ($ids as $id) {
				if (! $backend->delete($id)) {
					$result = false;
				}
			}
		} catch (PhalconCacheException $exception) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Removing of the data from the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		return $result;
	}
	
	/**
	 * Increment the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 * @throws OperationFailed
	 */
	public function inc($id, $value = 1) {
		$id      = $this->prepareId($id);
		$backend = $this->getBackend();
		
		// Get the counter
		try {
			$result = $backend->get($id);
		}
		catch (PhalconCacheException $exception) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Incrementing (get) of the data in the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		if ($result === null) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Incrementing (get) of the data in the cache failed, ID: "' . $id . '"');
			} else {
				return false;
			}
		}
		
		// Set the counter
		$newValue = $result + $value;
		
		try {
			$saveResult = $backend->save($id, $newValue);
		}
		catch (PhalconCacheException $exception) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Incrementing (set) of the data in the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		if ($saveResult === false) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Incrementing (set) of the data in the cache failed, ID: "' . $id . '"');
			} else {
				return false;
			}
		}
		
		return $newValue;
	}
	
	/**
	 * Decrement the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 * @throws OperationFailed
	 */
	public function dec($id, $value = 1) {
		$id      = $this->prepareId($id);
		$backend = $this->getBackend();
		
		// Get the counter
		try {
			$result = $backend->get($id);
		}
		catch (PhalconCacheException $exception) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Decrementing (get) of the data in the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		if ($result === null) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Decrementing (get) of the data in the cache failed, ID: "' . $id . '"');
			} else {
				return false;
			}
		}
		
		// Set the counter
		$newValue = $result + $value;
		
		try {
			$saveResult = $backend->save($id, $newValue);
		}
		catch (PhalconCacheException $exception) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Decrementing (set) of the data in the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		if ($saveResult === false) {
			if ($this->operationsExceptions) {
				throw new OperationFailed('Decrementing (set) of the data in the cache failed, ID: "' . $id . '"');
			} else {
				return false;
			}
		}
		
		return $newValue;
	}
	
	/**
	 * Prepare the ID
	 * 
	 * @param  string | array $id
	 * @return string
	 */
	protected function prepareId($id) {
		if (! is_scalar($id)) {
			$id = json_encode($id);
			
			if (isset($id[33])) {
				$id = md5($id);
			}
		}
		
		if ($this->namespace === null) {
			return $id;
		}
		
		return $this->namespace . '_' . $id;
	}
	
	/**
	 * Set the namespace of the data ID's
	 * 
	 * @param string $namespace
	 */
	public function setNamespace($namespace) {
		$this->namespace = (string) $namespace;
	}
	
	/**
	 * Get the namespace of the data ID's
	 * 
	 * @return string
	 */
	public function getNamespace() {
		return $this->namespace;
	}
	
	/**
	 * Set the phalcon cache backend
	 * 
	 * @param PhalconBackendInterface $backend
	 */
	public function setBackend(PhalconBackendInterface $backend) {
		$this->phalconBackend = $backend;
	}
	
	/**
	 * Get the phalcon cache backend
	 * 
	 * @return PhalconBackendInterface
	 * @throws NoBackend
	 */
	public function getBackend() {
		if ($this->phalconBackend === null) {
			throw new NoBackend('No phalcon cache backend was passed to the wrapper');
		}
		
		return $this->phalconBackend;
	}
	
	/**
	 * Set throws or not an operation exceptions
	 * 
	 * @param bool $throw
	 */
	public function setOperationExceptions($throw = true) {
		$this->operationsExceptions = (bool) $throw;
	}
	
	/**
	 * Get throws or not an operation exceptions
	 * 
	 * @return bool $throw
	 */
	public function getOperationExceptions() {
		return $this->operationsExceptions;
	}
	
	/**
	 * Get the cache topology
	 * 
	 * @return Descriptor
	 */
	public function getTopology() {
		$descriptor = new Descriptor('Phalcon', self::TOPOLOGY_BACKEND);
		$descriptor->id = $this->getTopologyId();
		
		$backend = $this->getBackend();
		$class   = get_class($backend);
		$title   = substr($class, strrpos($class, '\\') + 1);
		
		$descBackend = new Descriptor($title, self::TOPOLOGY_SPECIAL);
		
		$options = $backend->getOptions();
		
		if (! empty($options)) {
			$this->handleBackendOptions($descBackend, $options);
		}
		
		$descriptor[] = $descBackend;
		
		return $descriptor;
	}
	
	/**
	 * Handle the Phalcon backend options
	 * 
	 * @param Descriptor $descriptor
	 * @param array      $options
	 */
	protected function handleBackendOptions(Descriptor $descriptor, array $options) {
		if (isset($options['host'])) {
			$host = $options['host'];
			
			if (isset($options['port'])) {
				$host .= ':' . $options['port'];
			}
			
			$descriptor->host = $host;
		}
		
		if (isset($options['persistent'])) {
			$descriptor->persist = $options['persistent'] ? 'On' : 'Off';
		}
	}
	
}