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

use ZExt\Cache\Backend\Exceptions\NoPhpExtension;
use ZExt\Cache\Backend\Exceptions\OperationFailed;
use ZExt\Cache\Backend\Exceptions\ServerParamsError;

use ZExt\Cache\Topology\TopologyInterface;
use ZExt\Topology\Descriptor;


/**
 * Memcache backend adapter
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Backend
 * @author     Mike.Mirten
 * @version    1.0.2
 */
class Memcache implements BackendInterface, TopologyInterface {
	
	use OptionsTrait;
	
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
	 * Parameters to arguments conversion list
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
	 * Parameters:
	 * param name          | datatype | default | description
	 * ===========================================================================================
	 * servers             | array    | null    | Memcache servers params
	 * namespace           | string   | null    | Namespace of an IDs
	 * compression         | bool     | false   | Use compression of a data
	 * operationExceptions | bool     | true    | Throw the exceptions by operation errors
	 * client              | Memcache | null    | Configured memcache client instance if necessary
	 * 
	 * Servers parameters:
	 * param name    | datatype | default     | description
	 * ===========================================================================================
	 * host          | string   | '127.0.0.1' | IP address or the host name or the socket path
	 * port          | int      | 11211       | TCP port number
	 * persistent    | bool     | true        | Persistent connection (Will not be closed on script end, and can be reused)
	 * weight        | int      | 1           | Server weight in the servers pool
	 * timeout       | int      | 1           | Connection timeout in seconds
	 * retryInterval | int      | 15          | Connection retry interval in seconds
	 * 
	 * @param  array | Traversable $options
	 * @throws NoPhpExtension
	 */
	public function __construct($options = null) {
		if (! extension_loaded('memcache')) {
			throw new NoPhpExtension('The memcache php extension required for the backend');
		}
		
		if ($options !== null) {
			$this->setOptions($options, false, false);
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
		
		$result = $this->getClient()->get($this->prepareId($id));
		
		return $result === false ? null : $result;
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
		$result      = $this->getClient()->get($preparedIds);
		
		if ($this->namespace === null) {
			return $result;
		}
		
		$ids = array_intersect_key(array_combine($preparedIds, $id), $result);
		return array_combine(array_values($ids), array_values($result));
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
		
		$result = $this->getClient()->set($id, $data, $compression, $lifetime);
		
		if ($result === false && $this->operationsExceptions) {
			throw new OperationFailed('Inserting of the data into the cache failed, ID: "' . $id . '"');
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

		foreach ($data as $id => $dataPart) {
			$result = $client->set($id, $dataPart, $compression, $lifetime);

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
	 */
	public function has($id) {
		if (! $this->connectionCheckStatus) {
			$this->addServer();
		}
		
		return $this->getClient()->get($this->prepareId($id));
	}
	
	/**
	 * Remove the data from the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function remove($id) {
		if (! $this->connectionCheckStatus) {
			$this->addServer();
		}
		
		return $this->getClient()->delete($this->prepareId($id));
	}
	
	/**
	 * Remove the many the data from the cache
	 * 
	 * @param  array $id
	 * @return bool
	 */
	public function removeMany(array $ids) {
		if (! $this->connectionCheckStatus) {
			$this->addServer();
		}
		
		$client = $this->getClient();
		$ids    = array_map([$this, 'prepareId'], $ids);
		
		$result = true;
		
		foreach ($ids as $id) {
			if (! $client->delete($id)) {
				$result = false;
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
		if (! $this->connectionCheckStatus) {
			$this->addServer();
		}
		
		$id     = $this->prepareId($id);
		$result = $this->getClient()->increment($id, $value);
		
		if ($result === false && $this->operationsExceptions) {
			throw new OperationFailed('Incrementing of the data in the cache failed, ID: "' . $id . '"');
		}
		
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
		
		$id     = $this->prepareId($id);
		$result = $this->getClient()->decrement($id, $value);
		
		if ($result === false && $this->operationsExceptions) {
			throw new OperationFailed('Decrementing of the data in the cache failed, ID: "' . $id . '"');
		}
		
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
	
	/**
	 * Get the cache topology
	 * 
	 * @return Descriptor
	 */
	public function getTopology() {
		$descriptor = new Descriptor('Memcache', self::TOPOLOGY_BACKEND);
		
		if ($this->memcacheClient === null) {
			return $descriptor;
		}
		
		foreach($this->memcacheClient->getextendedstats() as $serverKey => $serverInfo) {
			$descriptor->setProperty(null, $serverKey);
			
			$filled = $serverInfo['bytes'] / $serverInfo['limit_maxbytes'] * 100;
			
			$descriptor->version = $serverInfo['version'];
			$descriptor->used    = $serverInfo['bytes'];
			$descriptor->total   = $serverInfo['limit_maxbytes'];
			$descriptor->filled  = round($filled, 2) . '%';
		}
		
		return $descriptor;
	}
	
}