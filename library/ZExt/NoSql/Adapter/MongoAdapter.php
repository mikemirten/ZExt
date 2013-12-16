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

namespace ZExt\NoSql\Adapter;

use ZExt\Profiler\ProfilerExtendedInterface,
	ZExt\Profiler\ProfileableTrait,
    ZExt\Profiler\ProfileableInterface;

use Traversable, Exception;

use	MongoClient, MongoDB, MongoCollection, MongoCursor, MongoConnectionException;

use ZExt\NoSql\Adapter\Exceptions\PhpExtensionError;
use ZExt\NoSql\Adapter\Exceptions\ConnectionError;
use ZExt\NoSql\Adapter\Exceptions\OptionsError;

/**
 * MongoDB Adapter
 * 
 * @category   ZExt
 * @package    NoSql
 * @subpackage MongoDbAdapter
 * @version    2.0beta
 */
class MongoAdapter implements ProfileableInterface {
	
	use ProfileableTrait;
	
	const PARAM_HOST     = 'host';
	const PARAM_HOSTS    = 'hosts';
	const PARAM_PORT     = 'port';
	const PARAM_USERNAME = 'username';
	const PARAM_PASSWORD = 'password';
	const PARAM_DATABASE = 'database';
	const PARAM_PROFILER = 'profiler';
	const PARAM_PARAMS   = 'params';
	
	const PARAM_CONNECTION_ATTEMPTS = 'connection_attempts';
	const PARAM_CONNECTION_TIMEOUT  = 'connection_timeout';
	
	const DEFAULT_CONNECTION_ATTEMPTS = 3;
	const DEFAULT_CONNECTION_TIMEOUT  = 1; // seconds
	
	/**
	 * Mongo client
	 * 
	 * @var MongoClient 
	 */
	protected $_client;
	
	/**
	 * Connection options
	 *
	 * @var array
	 */
	protected $_connectionOptions = [];

	/**
	 * Connection pool hosts
	 *
	 * @var array
	 */
	protected $_hosts = [];
	
	/**
	 * Default port for all hosts
	 *
	 * @var int
	 */
	protected $_port = MongoClient::DEFAULT_PORT;
	
	/**
	 * Default database
	 * 
	 * @var string
	 */
	protected $_dbname = 'test';
	
	/**
	 * Mongo databases
	 * 
	 * @var MongoDB[] 
	 */
	protected $_databases = [];
	
	/**
	 * Mongo collections
	 * 
	 * @var MongoCollection[] 
	 */
	protected $_collections = [];
	
	/**
	 * Connection attempts
	 *
	 * @var int
	 */
	protected $_connectionAttempts = self::DEFAULT_CONNECTION_ATTEMPTS;
	
	/**
	 * Timeout between connections' attempts in seconds
	 *
	 * @var int
	 */
	protected $_connectionTimeout = self::DEFAULT_CONNECTION_TIMEOUT;
	
	/**
	 * Constructor
	 * 
	 * @param  mixed $config
	 * @throws PhpExtensionError
	 */
	public function __construct($config = null) {
		if (! extension_loaded('mongo')) {
			throw new PhpExtensionError('Mongo extension is unavailable');
		}
		
		$driverVersion = phpversion('mongo');
		if ((version_compare($driverVersion, '1.3.0')) < 0) {
			throw new PhpExtensionError('Version ' . $driverVersion . ' of the Mongo extension is too old');
		}
		
		if ($config !== null) {
			$this->setConfig($config);
		}
	}
	
	/**
	 * Set the adapter's config
	 * 
	 * @param  array | Traversable | string $config
	 * @throws OptionsError
	 */
	public function setConfig($config) {
		if (is_string($config)) {
			$this->addHost($config);
			return;
		}
		
		if ($config instanceof Traversable) {
			$config = iterator_to_array($config);
		}
		
		if (! is_array($config)) {
			throw new OptionsError('Config must be an array, a traversable or a string');
		}
		
		if (isset($config[self::PARAM_PORT])) {
			$this->_port = (int) $config[self::PARAM_PORT];
			unset($config[self::PARAM_PORT]);
		}
		
		if (isset($config[self::PARAM_HOST])) {
			$this->addHost($config[self::PARAM_HOST]);
			unset($config[self::PARAM_HOST]);
		}
		else if (isset($config[self::PARAM_HOSTS])) {
			$this->setHosts($config[self::PARAM_HOSTS]);
			unset($config[self::PARAM_HOSTS]);
		}
		
		if (isset($config[self::PARAM_DATABASE])) {
			$this->_dbname = $config[self::PARAM_DATABASE];
			unset($config[self::PARAM_DATABASE]);
		}
		if (isset($config[self::PARAM_PROFILER])) {
			$this->_profilerEnabled = (bool) $config[self::PARAM_PROFILER];
			unset($config[self::PARAM_PROFILER]);
		}
		if (isset($config[self::PARAM_CONNECTION_ATTEMPTS])) {
			$this->_connectionAttempts = (int) $config[self::PARAM_CONNECTION_ATTEMPTS];
			unset($config[self::PARAM_CONNECTION_ATTEMPTS]);
		}
		if (isset($config[self::PARAM_CONNECTION_TIMEOUT])) {
			$this->_connectionTimeout = (int) $config[self::PARAM_CONNECTION_TIMEOUT];
			unset($config[self::PARAM_CONNECTION_TIMEOUT]);
		}
		
		if (! empty($config)) {
			$this->_connectionOptions = $config;
		}
	}
	
	/**
	 * Get a databases list
	 * 
	 * @return array
	 */
	public function getDatabases() {
		$client = $this->getClient();
		
		if ($this->_profilerEnabled) {
			$event = $this->getProfiler()->startRead('Databases list');
		}
		
		$databasesRaw = $client->listDBs();
		
		if ($this->_profilerEnabled) {
			$event->stopSuccess();
		}
		
		$databases = [];
		
		foreach ($databasesRaw['databases'] as $database) {
			$databases[] = $database['name'];
		}
		
		return $databases;
	}
	
	/**
	 * Get a collections list of a database
	 * 
	 * @param string $databaseName
	 */
	public function getCollections($databaseName = null) {
		$database = $this->getDatabase($databaseName);
		
		if ($this->_profilerEnabled) {
			$event = $this->getProfiler()->startRead('Collections list');
		}
		
		$collections = $database->getCollectionNames();
		
		if ($this->_profilerEnabled) {
			$event->stopSuccess();
		}
		
		return $collections;
	}
	
	/**
	 * Insert the data into the collection
	 * 
	 * @param string $collectionName
	 * @param array  $data
	 * @param array  $options
	 */
	public function insert($collectionName, array $data, array $options = []) {
		if ($this->_profilerEnabled) {
			$message = 'db.' . $collectionName . '.insert(' . json_encode($data) . ');';
			$event   = $this->getProfiler()->startInsert($message);
		}
			
		$this->getCollection($collectionName)->insert($data, $options);
		
		if ($this->_profilerEnabled) {
			$event->stopSuccess();
		}
	}
	
	/**
	 * Find the data from the collection
	 * 
	 * @param  string $collectionName
	 * @param  array  $criteria
	 * @param  array  $fields
	 * @return MongoCursor
	 */
	public function find($collectionName, array $criteria = [], array $fields = []) {
		if ($this->_profilerEnabled) {
			$message = 'db.' . $collectionName . '.find(';

			if (empty($criteria)) {
				$message .= ');';
			} else {
				$message .= json_encode($criteria) . ');';
			}

			$event = $this->getProfiler()->startRead($message);
		}

		$cursor = $this->getCollection($collectionName)->find($criteria, $fields);

		if ($this->_profilerEnabled) {
			$event->stopSuccess();
		}
		
		return $cursor;
	}
	
	/**
	 * Find an item of the data from the collection
	 * 
	 * @param string $collectionName
	 * @param array  $criteria
	 * @param array  $fields
	 */
	public function findFirst($collectionName, array $criteria = [], array $fields = []) {
		if ($this->_profilerEnabled) {
			$message = 'db.' . $collectionName . '.findOne(';

			if (empty($criteria)) {
				$message .= ');';
			} else {
				$message .= json_encode($criteria) . ');';
			}
			
			$event = $this->getProfiler()->startRead($message);
		}
		
		$result = $this->getCollection($collectionName)->findOne($criteria);
		
		if ($this->_profilerEnabled) {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Update the data of the collection
	 * 
	 * @param string $collectionName
	 * @param array  $criteria
	 * @param array  $data
	 * @param array  $options
	 */
	public function update($collectionName, array $criteria, array $data, array $options = []) {
		if ($this->_profilerEnabled) {
			$message = 'db.' . $collectionName . '.update(' .
				json_encode($criteria) . ',' . 
				json_encode($data) . ');';
			
			$event = $this->getProfiler()->startWrite($message);
		}
		
		$this->getCollection($collectionName)->update($criteria, $data, $options);
		
		if ($this->_profilerEnabled) {
			$event->stopSuccess();
		}
	}
	
	/**
	 * Remove the data from the collection
	 * 
	 * @param string $collectionName
	 * @param array  $criteria
	 * @param array  $options
	 */
	public function remove($collectionName, array $criteria = [], array $options = []) {
		if ($this->_profilerEnabled) {
			$message = 'db.remove(' . json_encode($criteria) . ');';
			$event   = $this->getProfiler()->startDelete($message);
		}
		
		$this->getCollection($collectionName)->remove($criteria, $options);
		
		if ($this->_profilerEnabled) {
			$event->stopSuccess();
		}
	}
	
	/**
	 * Get the collection
	 * 
	 * @param  string $collectionName
	 * @return MongoCollection
	 */
	public function getCollection($collectionName) {
		if (! isset($this->_collections[$collectionName])) {
			$this->_collections[$collectionName] = $this->getDatabase()->selectCollection($collectionName);
		}
		
		return $this->_collections[$collectionName];
	}
	
	/**
	 * Get the database
	 * 
	 * @param  string $databaseName
	 * @return MongoDB
	 */
	public function getDatabase($databaseName = null) {
		if ($databaseName === null) {
			$databaseName = $this->_dbname;
		}
		
		if (! isset($this->_databases[$databaseName])) {
			$this->_databases[$databaseName] = $this->getClient()->selectDB($databaseName);
		}
		
		return $this->_databases[$databaseName];
	}
	
	/**
	 * Set the mongo client
	 * 
	 * @param MongoClient $client
	 */
	public function setClient(MongoClient $client) {
		$this->_client = $client;
	}
	
	/**
	 * Get the mongo client
	 * 
	 * @return MongoClient
	 * @throws ConnectionError
	 */
	public function getClient() {
		if ($this->_client === null) {
			$connectionString  = $this->getConnectionString();
			$connectionOptions = $this->getConnectionOptions();
			
			if ($this->_profilerEnabled) {
				$event = $this->getProfiler()->startInfo('Connect: ' . $connectionString);
			}
			
			$attempts = $this->_connectionAttempts;
			
			do {
				try {
					$this->_client = new MongoClient($connectionString, $connectionOptions);
				}
				catch (MongoConnectionException $exception) {
					sleep($this->_connectionTimeout);
				}
				catch (Exception $exception) {
					if ($this->_profilerEnabled) {
						$event->stopError();
					}
				}
			} while (-- $attempts > 0);
			
			if ($this->_client === null) {
				if ($this->_profilerEnabled) {
					$event->stopError();
				}
				
				if (isset($exception)) {
					throw new ConnectionError('Unable to connect to the database', $exception->getCode(), $exception);
				} else {
					$exception = new ConnectionError('Unable to connect to the database, after ' . $this->_connectionAttempts . ' attempts');
				}
				
				throw $exception;
			}
			
			if ($this->_profilerEnabled) {
				if (isset($exception)) {
					$event->stopNotice();
				} else {
					$event->stopSuccess();
				}
				
				$this->putDetailInfoIntoProfiler();
			}
		}
		
		return $this->_client;
	}
	
	/**
	 * Connect to a database (should be used ONLY for development purposes)
	 */
	public function connect() {
		$this->getClient();
	}
	
	/**
	 * Close all opened connections
	 */
	public function disconnect() {
		if ($this->_client !== null) {
			$this->_client->close(true);
		}
	}
	
	/**
	 * Set the hosts of the connection pool
	 * 
	 * @param  array | Traversable $hosts
	 * @throws OptionsError
	 */
	public function setHosts($hosts) {
		$this->_hosts = [];
		
		if (! $hosts instanceof Traversable && ! is_array($hosts)) {
			throw new OptionsError('Hosts data must be an array or a traversable');
		}
		
		foreach ($hosts as $name => $host) {
			if ($host instanceof Traversable) {
				$host = iterator_to_array($host);
			}
			
			if (is_int($name)) {
				$this->addHost($host);
			} else {
				$this->addHost($host, $name);
			}
		}
	}
	
	/**
	 * Add the host to the connection pool
	 * 
	 * @param  string | array $host
	 * @param  string         $name
	 * @throws OptionsError
	 */
	public function addHost($host, $name = null) {
		if (is_string($host)) {
			$host = trim($host);
			
			// IP address as an url for the parse_url()
			if (! preg_match('~^[a-z]+://~i', $host)) {
				$host = 'mongodb://' . $host;
			}
			
			$host = parse_url($host);
			
			if (isset($host['query'])) {
				parse_str($host['query'], $host[self::PARAM_PARAMS]);
				ksort($host[self::PARAM_PARAMS]);
				unset($host['query']);
			}

			if (isset($host['user'])) {
				$host[self::PARAM_USERNAME] = $host['user'];
				unset($host['user']);
			}

			if (isset($host['pass'])) {
				$host[self::PARAM_PASSWORD] = $host['pass'];
				unset($host['pass']);
			}
		}
		
		if (! is_array($host)) {
			throw new OptionsError('Host parameter must be a string or an array, "' . gettype($host) . '" given.');
		}
		
		$host = array_map(function($in) {
			return is_string($in) ? trim($in) : $in;
		}, $host);
		
		if (isset($host[self::PARAM_PORT])) {
			$host[self::PARAM_PORT] = (int) $host[self::PARAM_PORT];
		}
		
		if ($name === null) {
			ksort($host);
			$name = crc32(json_encode($host));
		}
		
		if (! isset($this->_hosts[$name])) {
			$this->_hosts[$name] = $host;
		}
	}
	
	/**
	 * Get MongoClient connection string
	 * 
	 * @return string
	 */
	public function getConnectionString() {
		$hostsCount = count($this->_hosts);
		
		if ($hostsCount === 0) {
			$hosts = MongoClient::DEFAULT_HOST;
		} else if ($hostsCount === 1) {
			$hosts = $this->_assembleHost(current($this->_hosts));
		} else {
			$hosts = implode(',', array_map([$this, '_assembleHost'], $this->_hosts));
		}
		
		return 'mongodb://' . $hosts;
	}
	
	/**
	 * Assemble an url from the host parts
	 * 
	 * @param  array $host
	 * @return string
	 */
	protected function _assembleHost(array $host) {
		$hostString = '';

		// Authentication
		if (! empty($host[self::PARAM_USERNAME])) {
			$hostString .= $host[self::PARAM_USERNAME];

			if (! empty($host[self::PARAM_PASSWORD])) {
				$hostString .= ':' . $host[self::PARAM_PASSWORD];
			}

			$hostString .= '@';
			$loginUsed   = true;
		}

		// Host
		if (empty($host[self::PARAM_HOST])) {
			$hostString .= MongoClient::DEFAULT_HOST;
		} else {
			$hostTrimmed = $host[self::PARAM_HOST];
			$hostString .= $hostTrimmed;

			if (strrpos($hostTrimmed, '.sock') === (strlen($hostTrimmed) - 5)) {
				$socketUsed = true;
			}
		}

		// Port
		if (isset($loginUsed, $socketUsed)) {
			$hostString .= ':0';
		} else if (! empty($host[self::PARAM_PORT])) {
			if ($host[self::PARAM_PORT] !== MongoClient::DEFAULT_PORT) {
				$hostString .= ':' . $host[self::PARAM_PORT];
			}
		} else if ($this->_port !== MongoClient::DEFAULT_PORT) {
				$hostString .= ':' . $this->_port;
		}

		// Params
		if (! empty($host[self::PARAM_PARAMS])) {
			if (is_array($host[self::PARAM_PARAMS])) {
				$hostString .= '?' . http_build_query($host[self::PARAM_PARAMS]);
			} else {
				$hostString .= '?' . $host[self::PARAM_PARAMS];
			}
		}

		return $hostString;
	}

	/**
	 * Get a connection options
	 * 
	 * @return array
	 */
	public function getConnectionOptions() {
		return $this->_connectionOptions;
	}
	
	/**
	 * Profiler init callback
	 */
	protected function onProfilerInit($profiler) {
		$profiler->setIcon('data:image/png;base64,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');
		$profiler->setName('MongoDB');
	}
	
	/**
	 * Collect an additional information about the MongoDB and a connection and put it to a profiler
	 */
	protected function putDetailInfoIntoProfiler() {
		if (! $this->_profilerEnabled) {
			return;
		}
		
		$profiler = $this->getProfiler();

		if (! $profiler instanceof ProfilerExtendedInterface) {
			return;
		}

		$infoRaw = $this->getDatabase('admin')->command(['buildinfo' => 1]);
		
		$info = ['MongoDB version' => $infoRaw['version'] . ' (' . $infoRaw['bits'] . '-bit)'];

		foreach ($this->getClient()->getConnections() as $key => $connection) {
			$string = $connection['server']['host'] . ':' . $connection['server']['port'] . ' ';
			
			if (! empty($connection['server']['repl_set_name'])) {
				$string .= $connection['server']['repl_set_name'] . ':';
			}
			
			$string .= $connection['connection']['connection_type_desc'];
			$string .= ' ping: ' . $connection['connection']['ping_ms'] . 'ms';
			
			$info['Server ' . $key] = $string;
		}

		$profiler->setAdditionalInfo($info);
	}
	
	public function __sleep() {
		return [
			'_hosts',
			'_port',
			'_dbname',
			'_connectionOptions',
			'_profilerEnabled'
		];
	}
	
}