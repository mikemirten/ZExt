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

use ZExt\Components\OptionsTrait;

use ZExt\Profiler\ProfileableInterface;
use ZExt\Profiler\ProfileableTrait;
use ZExt\Profiler\ProfileInterface;

use ZExt\Cache\Backend\Exceptions\NoBackend;
use ZExt\Cache\Backend\Exceptions\OperationFailed;

/**
 * Phalcon cache backend adapter
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Backend
 * @author     Mike.Mirten
 * @version    1.0beta
 */
class PhalconWrapper implements BackendInterface, ProfileableInterface {
	
	use OptionsTrait;
	use ProfileableTrait;
	
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
	protected $operationsExceptions = false;
	
	/**
	 * Constructor
	 * 
	 * @param PhalconBackendInterface $backend
	 * @param array | Traversable     $options
	 */
	public function __construct(PhalconBackendInterface $backend = null, $options = null) {
		if ($backend !== null) {
			$this->setBackend($backend);
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
		$id = $this->prepareId($id);
		
		if ($this->_profilerEnabled) {
			$profile = $this->getProfiler()->startEvent('Get: ' . $id, ProfileInterface::TYPE_READ);
		}
		
		try {
			$result = $this->getBackend()->get($id);
		} catch (PhalconCacheException $exception) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Fetching of the data from the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return;
			}
		}
		
		if ($this->_profilerEnabled) {
			$profile->stop($result === null ? ProfileInterface::STATUS_NOTICE : ProfileInterface::STATUS_SUCCESS);
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
		
		if ($this->_profilerEnabled) {
			$logId   = implode(', ', $idPrepared);
			$profile = $this->getProfiler()->startEvent('Get (' . count($id) . '): ' . $logId, ProfileInterface::TYPE_READ);
		}
		
		$results = [];
		
		foreach ($idPrepared as $partId) {
			try {
				$result = $backend->get($partId);
			} catch (PhalconCacheException $exception) {
				if ($this->_profilerEnabled) {
					$profile->stop(ProfileInterface::STATUS_ERROR);
				}

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
		
		if ($this->_profilerEnabled) {
			$profile->stop(empty($results) ? ProfileInterface::STATUS_NOTICE : ProfileInterface::STATUS_SUCCESS);
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
		$id = $this->prepareId($id);
		
		if ($this->_profilerEnabled) {
			$profile = $this->getProfiler()->startEvent('Set: ' . $id, ProfileInterface::TYPE_WRITE);
		}
		
		try {
			$result = $this->getBackend()->save($id, $data, $lifetime);
		} catch (PhalconCacheException $exception) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}

			if ($this->operationsExceptions) {
				throw new OperationFailed('Inserting of the data into the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		if ($result === false) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}

			if ($this->operationsExceptions) {
				throw new OperationFailed('Inserting of the data into the cache failed, ID: "' . $id . '"');
			} else {
				return false;
			}
		}
		
		if ($this->_profilerEnabled) {
			$profile->stop($result === false ? ProfileInterface::STATUS_ERROR : ProfileInterface::STATUS_SUCCESS);
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
		
		if ($this->_profilerEnabled) {
			$idLog   = implode(', ', $ids);
			$profile = $this->getProfiler()->startEvent('Set (' . count($ids) . '): ' . $idLog, ProfileInterface::TYPE_WRITE);
		}
		
		foreach ($data as $id => $dataPart) {
			try {
				$result = $backend->save($id, $dataPart, $lifetime);
			} catch (PhalconCacheException $exception) {
				if ($this->_profilerEnabled) {
					$profile->stop(ProfileInterface::STATUS_ERROR);
				}

				if ($this->operationsExceptions) {
					throw new OperationFailed('Inserting of the data into the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
				} else {
					return false;
				}
			}
			
			if ($result === false) {
				if ($this->_profilerEnabled) {
					$profile->stop(ProfileInterface::STATUS_ERROR);
				}

				if ($this->operationsExceptions) {
					throw new OperationFailed('Inserting of the data into the cache failed, ID: "' . $id . '"');
				} else {
					return false;
				}
			}
		}
		
		if ($this->_profilerEnabled) {
			$profile->stop(ProfileInterface::STATUS_SUCCESS);
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
		$id = $this->prepareId($id);
		
		if ($this->_profilerEnabled) {
			$profile = $this->getProfiler()->startEvent('Has: ' . $id, ProfileInterface::TYPE_READ);
		}
		
		try {
			$result = $this->getBackend()->exists($id);
		} catch (PhalconCacheException $exception) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Checking of the data exiting in the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		if ($this->_profilerEnabled) {
			$profile->stop($result === false ? ProfileInterface::STATUS_NOTICE : ProfileInterface::STATUS_SUCCESS);
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
		$id = $this->prepareId($id);
		
		if ($this->_profilerEnabled) {
			$profile = $this->getProfiler()->startEvent('Remove: ' . $id, ProfileInterface::TYPE_DELETE);
		}
		
		try {
			$result = $this->getBackend()->delete($id);
		} catch (PhalconCacheException $exception) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Removing of the data from the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		if ($result === false) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Removing of the data from the cache failed, ID: "' . $id . '"');
			} else {
				return false;
			}
		}
		
		if ($this->_profilerEnabled) {
			$profile->stop(ProfileInterface::STATUS_SUCCESS);
		}
		
		return true;
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
		
		if ($this->_profilerEnabled) {
			$idLog   = implode(', ', $ids);
			$profile = $this->getProfiler()->startEvent('Remove (' . count($ids) . '): ' . $idLog, ProfileInterface::TYPE_DELETE);
		}
		
		try {
			foreach ($ids as $id) {
				$result = $backend->delete($id);
				
				if ($result === false) {
					if ($this->_profilerEnabled) {
						$profile->stop(ProfileInterface::STATUS_ERROR);
					}

					if ($this->operationsExceptions) {
						throw new OperationFailed('Removing of the data from the cache failed, ID: "' . $id . '"');
					} else {
						return false;
					}
				}
			}
		} catch (PhalconCacheException $exception) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Removing of the data from the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		if ($this->_profilerEnabled) {
			$profile->stop(ProfileInterface::STATUS_SUCCESS);
		}
		
		return true;
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
		
		if ($this->_profilerEnabled) {
			$profile = $this->getProfiler()->startEvent('Inc: ' . $id, ProfileInterface::TYPE_WRITE);
		}
		
		// Get the counter
		try {
			$result = $backend->get($id);
		} catch (PhalconCacheException $exception) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Incrementing (get) of the data in the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		if ($result === null) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
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
		} catch (PhalconCacheException $exception) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Incrementing (set) of the data in the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		if ($saveResult === false) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Incrementing (set) of the data in the cache failed, ID: "' . $id . '"');
			} else {
				return false;
			}
		}
		
		if ($this->_profilerEnabled) {
			$profile->stop(ProfileInterface::STATUS_SUCCESS);
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
		
		if ($this->_profilerEnabled) {
			$profile = $this->getProfiler()->startEvent('Dec: ' . $id, ProfileInterface::TYPE_WRITE);
		}
		
		// Get the counter
		try {
			$result = $backend->get($id);
		} catch (PhalconCacheException $exception) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Decrementing (get) of the data in the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		if ($result === null) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
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
		} catch (PhalconCacheException $exception) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Decrementing (set) of the data in the cache failed due to the backend exception occurred: "' . $exception->getMessage() . '"', 0, $exception);
			} else {
				return false;
			}
		}
		
		if ($saveResult === false) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Decrementing (set) of the data in the cache failed, ID: "' . $id . '"');
			} else {
				return false;
			}
		}
		
		if ($this->_profilerEnabled) {
			$profile->stop(ProfileInterface::STATUS_SUCCESS);
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
	
}