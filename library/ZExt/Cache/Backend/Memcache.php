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

use Memcache as MemcacheClient;

use ZExt\Components\OptionsTrait;

use ZExt\Profiler\ProfileableInterface;
use ZExt\Profiler\ProfileableTrait;
use ZExt\Profiler\ProfileInterface;

use ZExt\Cache\Backend\Exceptions\OperationFailed;
use ZExt\Cache\Backend\Exceptions\ServerParamsError;

/**
 * Memcache backend adapter
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Memcache
 * @author     Mike.Mirten
 * @version    1.0rc1
 */
class Memcache implements BackendInterface, ProfileableInterface {
	
	use OptionsTrait;
	use ProfileableTrait;
	
	// Server params names
	const SRV_PARAM_HOST           = 'host';
	const SRV_PARAM_PORT           = 'port';
	const SRV_PARAM_PERSISTENT     = 'persistent';
	const SRV_PARAM_WEIGHT         = 'weight';
	const SRV_PARAM_TIMEOUT        = 'timeout';
	const SRV_PARAM_RETRY_INTERVAL = 'retry_interval';
	
	// Server connection defaults
	const DEFAULT_HOST           = '127.0.0.1';
	const DEFAULT_PORT           = 11211;
	const DEFAULT_PERSISTENT     = true;
	const DEFAULT_WEIGHT         = 1;
	const DEFAULT_TIMEOUT        = 1;
	const DEFAULT_RETRY_INTERVAL = 15;
	
	// addServer() arguments
	const ARG_HOST           = 0;
	const ARG_PORT           = 1;
	const ARG_PERSISTENT     = 2;
	const ARG_WEIGHT         = 3;
	const ARG_TIMEOUT        = 4;
	const ARG_RETRY_INTERVAL = 5;
	
	/**
	 * Default arguments values for the addServer()
	 *
	 * @var array
	 */
	static protected $defaultServerArgs = [
		self::ARG_HOST           => self::DEFAULT_HOST,
		self::ARG_PORT           => self::DEFAULT_PORT,
		self::ARG_PERSISTENT     => self::DEFAULT_PERSISTENT,
		self::ARG_WEIGHT         => self::DEFAULT_WEIGHT,
		self::ARG_TIMEOUT        => self::DEFAULT_TIMEOUT,
		self::ARG_RETRY_INTERVAL => self::DEFAULT_RETRY_INTERVAL,
	];
	
	/**
	 * Parameters to argumrnts conversion list
	 *
	 * @var array
	 */
	static protected $serverParamsToArgs = [
		self::SRV_PARAM_HOST           => self::ARG_HOST,
		self::SRV_PARAM_PORT           => self::ARG_PORT,
		self::SRV_PARAM_PERSISTENT     => self::ARG_PERSISTENT,
		self::SRV_PARAM_WEIGHT         => self::ARG_WEIGHT,
		self::SRV_PARAM_TIMEOUT        => self::ARG_TIMEOUT,
		self::SRV_PARAM_RETRY_INTERVAL => self::ARG_RETRY_INTERVAL
	];
	
	/**
	 * Instance of the memcache client
	 *
	 * @var MemcacheClient
	 */
	private $memcacheClient;
	
	/**
	 * Compression using
	 *
	 * @var bool
	 */
	protected $compression = false;
	
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
	 * Has at least one server in the connection pool
	 *
	 * @var bool
	 */
	protected $connectionCheckStatus = false;
	
	/**
	 * Constructor
	 * 
	 * @param array | Traversable $options
	 */
	public function __construct($options = null) {
		if ($options !== null) {
			$this->setOptions($options);
		}
	}
	
	/**
	 * Fetch the data from the cache
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	public function get($id) {
		if (! $this->connectionCheckStatus) {
			$this->addServer();
		}
		
		$id = $this->prepareId($id);
		
		if ($this->_profilerEnabled) {
			$profile = $this->getProfiler()->startEvent('Get: ' . $id, ProfileInterface::TYPE_READ);
		}
		
		$result = $this->getClient()->get($id);
		
		if ($result === false) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_NOTICE);
			}
			
			return;
		}
		
		if ($this->_profilerEnabled) {
			$profile->stop(ProfileInterface::STATUS_SUCCESS);
		}
		
		return $result;
	}
	
	/**
	 * Fetch the many of the data from the cache
	 * 
	 * @param  array $id
	 * @return array
	 */
	public function getMany(array $id) {
		if (! $this->connectionCheckStatus) {
			$this->addServer();
		}
		
		$preparedIds = array_map([$this, 'prepareId'], $id);
		
		if ($this->_profilerEnabled) {
			$logId   = implode(', ', $preparedIds);
			$profile = $this->getProfiler()->startEvent('Get (' . count($id) . '): ' . $logId, ProfileInterface::TYPE_READ);
		}
		
		$result = $this->getClient()->get($preparedIds);
		
		if (empty($result)) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_NOTICE);
			}
			
			return $result;
		}
		
		if ($this->_profilerEnabled) {
			$profile->stop(ProfileInterface::STATUS_SUCCESS);
		}
		
		if ($this->namespace !== null) {
			$ids = array_intersect_key(array_combine($preparedIds, $id), $result);
			return array_combine(array_values($ids), array_values($result));
		}
		
		return $result;
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
		if (! $this->connectionCheckStatus) {
			$this->addServer();
		}
		
		$id          = $this->prepareId($id);
		$compression = $this->compression ? MEMCACHE_COMPRESSED : 0;
		
		if ($this->_profilerEnabled) {
			$profile = $this->getProfiler()->startEvent('Set: ' . $id, ProfileInterface::TYPE_WRITE);
		}
		
		$result = $this->getClient()->set($id, $data, $compression, $lifetime);
		
		if ($result === false) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Inserting of the data into the cache failed, ID: "' . $id . '"');
			}
		} else if ($this->_profilerEnabled) {
			$profile->stop(ProfileInterface::STATUS_SUCCESS);
		}
		
		return $result;
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
		if (! $this->connectionCheckStatus) {
			$this->addServer();
		}
		
		$compression = $this->compression ? MEMCACHE_COMPRESSED : 0;
		$client      = $this->getClient();
		
		$ids  = array_map([$this, 'prepareId'], array_keys($data));
		$data = array_combine($ids, array_values($data));
		
		if ($this->_profilerEnabled) {
			$idLog   = implode(', ', $ids);
			$profile = $this->getProfiler()->startEvent('Set (' . count($ids) . '): ' . $idLog, ProfileInterface::TYPE_WRITE);
		}

		foreach ($data as $id => $dataPart) {
			$result = $client->set($id, $dataPart, $compression, $lifetime);

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
	 */
	public function has($id) {
		if (! $this->connectionCheckStatus) {
			$this->addServer();
		}
		
		$id = $this->prepareId($id);
		
		if ($this->_profilerEnabled) {
			$profile = $this->getProfiler()->startEvent('Has: ' . $id, ProfileInterface::TYPE_READ);
		}
		
		$result = $this->getClient()->get($id);
		
		if ($result === false) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_NOTICE);
			}
			
			return false;
		}
		
		if ($this->_profilerEnabled) {
			$profile->stop(ProfileInterface::STATUS_SUCCESS);
		}
		
		return true;
	}
	
	/**
	 * Remove the data from the cache
	 * 
	 * @param  string $id
	 * @return bool
	 * @throws OperationFailed
	 */
	public function remove($id) {
		if (! $this->connectionCheckStatus) {
			$this->addServer();
		}
		
		$id = $this->prepareId($id);
		
		if ($this->_profilerEnabled) {
			$profile = $this->getProfiler()->startEvent('Remove: ' . $id, ProfileInterface::TYPE_DELETE);
		}
		
		$result = $this->getClient()->delete($id);
		
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
		
		$profile->stop(ProfileInterface::STATUS_SUCCESS);
		
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
		if (! $this->connectionCheckStatus) {
			$this->addServer();
		}
		
		$id = $this->prepareId($id);
		
		if ($this->_profilerEnabled) {
			$profile = $this->getProfiler()->startEvent('Inc: ' . $id, ProfileInterface::TYPE_WRITE);
		}
		
		$result = $this->getClient()->increment($id, $value);
		
		if ($result === false) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Incrementing of the data in the cache failed, ID: "' . $id . '"');
			} else {
				return false;
			}
		}
		
		$profile->stop(ProfileInterface::STATUS_SUCCESS);
		
		return $result;
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
		if (! $this->connectionCheckStatus) {
			$this->addServer();
		}
		
		$id = $this->prepareId($id);
		
		if ($this->_profilerEnabled) {
			$profile = $this->getProfiler()->startEvent('Dec: ' . $id, ProfileInterface::TYPE_WRITE);
		}
		
		$result = $this->getClient()->decrement($id, $value);
		
		if ($result === false) {
			if ($this->_profilerEnabled) {
				$profile->stop(ProfileInterface::STATUS_ERROR);
			}
			
			if ($this->operationsExceptions) {
				throw new OperationFailed('Decrementing of the data in the cache failed, ID: "' . $id . '"');
			} else {
				return false;
			}
		}
		
		$profile->stop(ProfileInterface::STATUS_SUCCESS);
		
		return $result;
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
	 * Set the compression using
	 * 
	 * @param bool $compression
	 */
	public function setCompression($compression) {
		$this->compression = (bool) $compression;
	}
	
	/**
	 * Set the compression using
	 * 
	 * @return bool
	 */
	public function getCompression() {
		return $this->compression;
	}
	
	/**
	 * Alias to the addServers()
	 * 
	 * @param array $servers
	 */
	public function setServers(array $servers) {
		$this->addServers($servers);
	}
	
	/**
	 * Add the servers to the client
	 * 
	 * @param array $servers
	 */
	public function addServers(array $servers) {
		foreach ($servers as $server) {
			$this->addServer($server);
		}
	}
	
	/**
	 * Add the server to the client
	 * 
	 * Parameters can be passed as a separate args of the method.
	 * Or as an array with the keys as the params names:
	 * <ul>
	 * <li>host</li>
	 * <li>port</li>
	 * <li>persistent</li>
	 * <li>weight</li>
	 * <li>timeout</li>
	 * <li>retry_interval</li>
	 * </ul>
	 * 
	 * All params are not required.
	 * Without parameters at all will be used defaults.
	 * 
	 * @param  string $host | array $params Host name or an address or a socket name | array with params
	 * @param  int    $port                 Port
	 * @param  bool   $persistent           Persistent connection
	 * @param  int    $weight               Server weight
	 * @param  int    $timeout              Server request timeout in seconds
	 * @param  int    $retryInterval        Server retry timeout in seconds
	 * @throws ServerParamsError
	 */
	public function addServer() {
		$argsRaw = func_get_args();
		
		if (isset($argsRaw[0]) && is_array($argsRaw[0])) {
			$args = self::$defaultServerArgs;
			
			foreach ($argsRaw[0] as $param => $value) {
				if (isset(self::$serverParamsToArgs[$param])) {
					$args[self::$serverParamsToArgs[$param]] = $value;
				}
			}
		} else {
			$args = $argsRaw + self::$defaultServerArgs;
		}

		$result = call_user_func_array([$this->getClient(), 'addServer'], $args);
		
		if ($result === false) {
			throw new ServerParamsError('Unable to add the server to the memcache connection pool');
		} else {
			$this->connectionCheckStatus = true;
		}
	}
	
	/**
	 * Get the client
	 * 
	 * @return MemcacheClient
	 */
	public function getClient() {
		if ($this->memcacheClient === null) {
			$this->memcacheClient = new MemcacheClient();
		}
		
		return $this->memcacheClient;
	}
	
	/**
	 * Set the client
	 * 
	 * @param MemcacheClient $client
	 */
	public function setClient(MemcacheClient $client) {
		$this->memcacheClient        = $client;
		$this->connectionCheckStatus = (@$client->getStats() !== false);
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
	
}