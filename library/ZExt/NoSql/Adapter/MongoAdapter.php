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
		$collection = $this->getCollection($collectionName);
		
		if ($this->_profilerEnabled) {
			$message = 'db.' . $collectionName . '.insert(' . json_encode($data) . ');';
			$event   = $this->getProfiler()->startInsert($message);
		}

		$collection->insert($data, $options);

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
		$collection = $this->getCollection($collectionName);
		
		if ($this->_profilerEnabled) {
			$message = 'db.' . $collectionName . '.find(';

			if (empty($criteria)) {
				$message .= ');';
			} else {
				$message .= json_encode($criteria) . ');';
			}

			$event = $this->getProfiler()->startRead($message);
		}

		$cursor = $collection->find($criteria, $fields);

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
		$collection = $this->getCollection($collectionName);
		
		if ($this->_profilerEnabled) {
			$message = 'db.' . $collectionName . '.findOne(';

			if (empty($criteria)) {
				$message .= ');';
			} else {
				$message .= json_encode($criteria) . ');';
			}

			$event = $this->getProfiler()->startRead($message);
		}

		$result = $collection->findOne($criteria, $fields);

		if ($this->_profilerEnabled) {
			$event->stopSuccess();
		}

		return $result;
	}
	
	/**
	 * Aggregate data by the pipeline
	 * 
	 * @param  string $collectionName
	 * @param  array  $pipeline
	 * @return array | null
	 */
	public function aggregate($collectionName, array $pipeline) {
		$collection = $this->getCollection($collectionName);
		
		if ($this->_profilerEnabled) {
			$message = 'db.' . $collectionName . '.aggregate(' . json_encode($pipeline) . ');';
			$event   = $this->getProfiler()->startRead($message);
		}

		$result = $collection->aggregate($pipeline);
		
		if ($this->_profilerEnabled) {
			if ($result['ok'] == 1) {
				$event->stopSuccess();
			} else {
				$event->stopError();
			}
		}
		
		if (($result['ok'] == 0)) {
			throw new Exceptions\OperationError('Aggregation error: "' . $result['errmsg'] . '"', $result['code']);
		}
		
		if (! empty($result['result'])) {
			return $result['result'];
		}
	}

	/**
	 * Update the data of the collection
	 * 
	 * @param  string $collectionName
	 * @param  array  $criteria
	 * @param  array  $data
	 * @param  array  $options
	 * @return bool
	 */
	public function update($collectionName, array $criteria, array $data, array $options = []) {
		$collection = $this->getCollection($collectionName);
		
		if ($this->_profilerEnabled) {
			$message = 'db.' . $collectionName . '.update(' .
				json_encode($criteria) . ',' . 
				json_encode($data) . ');';

			$event = $this->getProfiler()->startWrite($message);
		}

		$result = $collection->update($criteria, $data, $options);

		if ($this->_profilerEnabled) {
			$event->stopSuccess();
		}
		
		return $result;
	}

	/**
	 * Remove the data from the collection
	 * 
	 * @param  string $collectionName
	 * @param  array  $criteria
	 * @param  array  $options
	 * @return bool
	 */
	public function remove($collectionName, array $criteria = [], array $options = []) {
		$collection = $this->getCollection($collectionName);
		
		if ($this->_profilerEnabled) {
			$message = 'db.remove(' . json_encode($criteria) . ');';
			$event   = $this->getProfiler()->startDelete($message);
		}

		$result = $collection->remove($criteria, $options);

		if ($this->_profilerEnabled) {
			$event->stopSuccess();
		}
		
		return $result;
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
		$profiler->setIcon('dbmongo');
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

		$info = [
			'MongoDB version'    => $infoRaw['version'] . ' (' . $infoRaw['bits'] . '-bit)',
			'PHP driver version' => MongoClient::VERSION
		];

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