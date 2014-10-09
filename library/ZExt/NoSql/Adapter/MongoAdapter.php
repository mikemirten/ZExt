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

use ZExt\Profiler\ProfilerInterface,
    ZExt\Profiler\ProfilerExtendedInterface,
    ZExt\Profiler\ProfileableTrait,
    ZExt\Profiler\ProfileableInterface;

use ZExt\Components\OptionsTrait;
use ZExt\Url\Url;

use Traversable, Exception;

use	MongoClient, MongoDB, MongoCollection, MongoCursor, MongoConnectionException;

/**
 * MongoDB Adapter
 * 
 * @category   ZExt
 * @package    NoSql
 * @subpackage MongoDbAdapter
 * @version    3.0beta
 */
class MongoAdapter implements ProfileableInterface {

	use ProfileableTrait;
	use OptionsTrait;

	const DEFAULT_CONNECTION_ATTEMPTS = 3;
	const DEFAULT_CONNECTION_TIMEOUT  = 1; // seconds

	/**
	 * Mongo client
	 * 
	 * @var MongoClient 
	 */
	protected $client;

	/**
	 * Connection pool hosts
	 *
	 * @var Url[]
	 */
	protected $hosts = [];

	/**
	 * Default host
	 *
	 * @var string
	 */
	protected $defaultHost = MongoClient::DEFAULT_HOST;
	
	/**
	 * Default port for all hosts
	 *
	 * @var int
	 */
	protected $defaultPort = MongoClient::DEFAULT_PORT;
	
	/**
	 * Username
	 *
	 * @var string
	 */
	protected $username;
	
	/**
	 * Password
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Default database
	 * 
	 * @var string
	 */
	protected $dbname = 'test';

	/**
	 * Mongo databases
	 * 
	 * @var MongoDB[] 
	 */
	protected $databases = [];

	/**
	 * Mongo collections
	 * 
	 * @var MongoCollection[] 
	 */
	protected $collections = [];

	/**
	 * Connection attempts
	 *
	 * @var int
	 */
	protected $connectionAttempts = self::DEFAULT_CONNECTION_ATTEMPTS;

	/**
	 * Timeout between connections' attempts in seconds
	 *
	 * @var int
	 */
	protected $connectionTimeout = self::DEFAULT_CONNECTION_TIMEOUT;
	
	/**
	 * Client connection options
	 *
	 * @var array
	 */
	protected $connectionOptions = [];

	/**
	 * Constructor
	 * 
	 * @param  mixed $options
	 * @throws Exceptions\PhpExtensionError
	 */
	public function __construct($options = null) {
		if (! extension_loaded('mongo')) {
			throw new Exceptions\PhpExtensionError('Mongo extension is unavailable');
		}

		$driverVersion = phpversion('mongo');
		if ((version_compare($driverVersion, '1.3.0')) < 0) {
			throw new Exceptions\PhpExtensionError('Version ' . $driverVersion . ' of the Mongo extension is too old');
		}
		
		if ($options !== null) {
			$this->setOptions($options, false, false);
		}
	}

	/**
	 * Set default host for each server
	 * 
	 * @param  string $host
	 * @return MongoAdapter
	 */
	public function setDefaultHost($host, $port = null) {
		$this->defaultHost = (string) $host;
		
		if ($port !== null) {
			$this->setDefaultPort($port);
		}
		
		return $this;
	}
	
	/**
	 * Set default port for each server
	 * 
	 * @param  string $host
	 * @param  int    $port
	 * @return MongoAdapter
	 */
	public function setDefaultPort($port) {
		$this->defaultPort = (int) $port;
		
		return $this;
	}
	
	/**
	 * Set connection username
	 * 
	 * @param  string $username
	 * @param  string $password
	 * @return MongoAdapter
	 */
	public function setUsername($username, $password = null) {
		$this->username = (string) $username;
		
		if ($password !== null) {
			$this->setPassword($password);
		}
		
		return $this;
	}
	
	/**
	 * Set connection password
	 * 
	 * @param  string $password
	 * @return MongoAdapter
	 */
	public function setPassword($password) {
		$this->password = (string) $password;
		
		return $this;
	}
	
	/**
	 * Set database name
	 * 
	 * @param  string $name
	 * @return MongoAdapter
	 */
	public function setDBName($name) {
		$this->dbname = (string) $name;
		
		return $this;
	}
	
	/**
	 * Set the hosts of the connection pool
	 * 
	 * @param  array | Traversable $hosts
	 * @throws Exceptions\OptionsError
	 * @return MongoAdapter
	 */
	public function setHosts($hosts) {
		$this->hosts = [];

		if (! $hosts instanceof Traversable && ! is_array($hosts)) {
			throw new Exceptions\OptionsError('Hosts data must be an array or a traversable');
		}

		foreach ($hosts as $name => $host) {
			if (is_int($name)) {
				$this->addHost($host);
				continue;
			}
			
			$this->addHost($host, $name);
		}
		
		return $this;
	}

	/**
	 * Add the host to the connection pool
	 * 
	 * @param  string | array $host
	 * @param  string         $name
	 * @throws OptionsError
	 * @return MongoAdapter
	 */
	public function addHost($host, $name = null) {
		if (! $host instanceof Url) {
			if (is_string($host) && ! preg_match('~^[a-z]://~', $host)) {
				$host = 'mongo://' . $host;
			}
			
			$host = new Url($host);
		}
		
		if ($host->hasUsername()) {
			$this->setUsername($host->getUsername());
			
			
			if ($host->hasPassword()) {
				$this->setPassword($host->getPassword());
			}
		}
		
		$host->removeScheme();
		$host->removeUsername();
		$host->removePassword();
		
		if ($name === null) {
			$this->hosts[] = $host;
			return $this;
		}
		
		$this->hosts[$name] = $host;
		return $this;
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
	 * @param  string $collectionName
	 * @param  array  $data
	 * @param  array  $options
	 * @return bool
	 */
	public function insert($collectionName, array $data, array $options = []) {
		$collection = $this->getCollection($collectionName);
		
		if ($this->_profilerEnabled) {
			$message = 'db.' . $collectionName . '.insert(' . json_encode($data) . ');';
			$event   = $this->getProfiler()->startInsert($message);
		}

		$result  = $collection->insert($data, $options);
		$success = (bool) $result['ok'];
		
		if ($this->_profilerEnabled) {
			$success
				? $event->stopSuccess()
				: $event->stopError();
		}
		
		return $success;
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

		$result  = $collection->aggregate($pipeline);
		$success = (bool) $result['ok'];
		
		if ($this->_profilerEnabled) {
			$success
				? $event->stopSuccess()
				: $event->stopError();
		}
		
		if (! $success) {
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

		$result  = $collection->update($criteria, $data, $options);
		$success = (bool) $result['ok'];
		
		if ($this->_profilerEnabled) {
			$success
				? $event->stopSuccess()
				: $event->stopError();
		}
		
		return $success;
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

		$result  = $collection->remove($criteria, $options);
		$success = (bool) $result['ok'];
		
		if ($this->_profilerEnabled) {
			$success
				? $event->stopSuccess()
				: $event->stopError();
		}
		
		return $success;
	}

	/**
	 * Get the collection
	 * 
	 * @param  string $collectionName
	 * @return MongoCollection
	 */
	public function getCollection($collectionName) {
		if (! isset($this->collections[$collectionName])) {
			$this->collections[$collectionName] = $this->getDatabase()->selectCollection($collectionName);
		}

		return $this->collections[$collectionName];
	}

	/**
	 * Get the database
	 * 
	 * @param  string $databaseName
	 * @return MongoDB
	 */
	public function getDatabase($databaseName = null) {
		if ($databaseName === null) {
			$databaseName = $this->dbname;
		}

		if (! isset($this->databases[$databaseName])) {
			$this->databases[$databaseName] = $this->getClient()->selectDB($databaseName);
		}

		return $this->databases[$databaseName];
	}

	/**
	 * Set the mongo client
	 * 
	 * @param  MongoClient $client
	 * @return MongoAdapter
	 */
	public function setClient(MongoClient $client) {
		$this->client = $client;
		
		return $this;
	}

	/**
	 * Get the mongo client
	 * 
	 * @return MongoClient
	 * @throws Exceptions\ConnectionError
	 */
	public function getClient() {
		if ($this->client === null) {
			$connectionString  = $this->getConnectionString();
			$connectionOptions = $this->getConnectionOptions();

			if ($this->_profilerEnabled) {
				$event = $this->getProfiler()->startInfo('Connect: ' . $connectionString);
			}

			$attempts = $this->connectionAttempts;

			do {
				try {
					$this->client = new MongoClient($connectionString, $connectionOptions);
				}
				catch (MongoConnectionException $exception) {
					sleep($this->connectionTimeout);
				}
				catch (Exception $exception) {
					if ($this->_profilerEnabled) {
						$event->stopError();
					}
				}
			} while ($this->client === null && -- $attempts > 0);

			if ($this->client === null) {
				if ($this->_profilerEnabled) {
					$event->stopError();
				}

				if (isset($exception)) {
					throw new Exceptions\ConnectionError('Unable to connect to the database', $exception->getCode(), $exception);
				} else {
					$exception = new Exceptions\ConnectionError('Unable to connect to the database, after ' . $this->connectionAttempts . ' attempts');
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

		return $this->client;
	}

	/**
	 * Close all opened connections
	 */
	public function disconnect() {
		if ($this->client !== null) {
			$this->client->close(true);
		}
	}

	/**
	 * Get MongoClient connection string
	 * 
	 * @return string
	 */
	public function getConnectionString() {
		$connection = new Url('mongodb');
		$connection->setPath($this->dbname);
		
		if ($this->username !== null) {
			$connection->setUsername($this->username);
			
			if ($this->password !== null) {
				$connection->setPassword($this->password);
			}
		}
		
		if (empty($this->hosts)) {
			$connection->setHost($this->defaultHost)
			           ->setPort($this->defaultPort);
			
			return $connection->assemble();
		}
		
		$hosts = array_map([$this, 'assembleHost'], $this->hosts);
		$connection->setHost(implode(',', $hosts));
		
		return $connection->assemble();
	}

	/**
	 * Assemble an url from the host parts
	 * 
	 * @param  Url $host
	 * @return string
	 */
	protected function assembleHost(Url $host) {
		if (! $host->hasHost()) {
			$host->setHost($this->defaultHost);
		}
		
		if (preg_match('/\.sock$/i', $host->getHost())) {
			$host->removePort();
		} else if (! $host->hasPort()) {
			$host->setPort($this->defaultPort);
		}
		
		return trim($host->assemble(), '/');
	}

	/**
	 * Get a connection options
	 * 
	 * @return array
	 */
	public function getConnectionOptions() {
		return $this->connectionOptions;
	}

	/**
	 * On unknown option callback
	 * 
	 * @param string $option
	 * @param mixed  $value
	 */
	protected function onUnknownOptionSet($option, $value) {
		$this->connectionOptions[$option] = $value;
	}
	
	/**
	 * On profiler init callback
	 * 
	 * @param ProfilerInterface $profiler
	 */
	protected function onProfilerInit(ProfilerInterface $profiler) {
		$profiler->setName('MongoDB')
		         ->setIcon('dbmongo');
	}

	/**
	 * Collect an additional information about the MongoDB and a connection and put it to a profiler
	 */
	protected function putDetailInfoIntoProfiler() {
		$profiler = $this->getProfiler();

		if (! $profiler instanceof ProfilerExtendedInterface) {
			return;
		}

		try {
			$infoRaw = $this->getDatabase()->command(['buildinfo' => 1]);
		} catch (Exception $exception) {
			$profiler->setAdditionalInfo([
				'Buildinfo command error' => $exception->getMessage()
			]);
			
			return;
		}

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
			'defaultHost',
			'defaultPort',
			'hosts',
			'port',
			'username',
			'password',
			'dbname',
			'connectionOptions',
			'_profilerEnabled'
		];
	}

}