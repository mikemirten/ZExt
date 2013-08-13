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

namespace ZExt\Log;

use ZExt\Di\LocatorAwareInterface;

use ZExt\Log\Exceptions\NoLogger,
    ZExt\Log\Exceptions\NoFactory;

use Exception;

/**
 * Logger aware trait
 * 
 * @category   ZExt
 * @package    Logger
 * @subpackage Logger
 * @author     Mike.Mirten
 * @version    1.1
 */
trait LoggerAwareTrait {
	
	/**
	 * Logger
	 *
	 * @var LoggerInterface
	 */
	private $_logger;
	
	/**
	 * Loggers' factory
	 *
	 * @var FactoryInterface
	 */
	private $_loggersFactory;
	
	/**
	 * Id of a logger's service in services' locator
	 *
	 * @var string
	 */
	private $_loggerServiceId = 'logger';
	
	/**
	 * Id of a loggers' factory service in services' locator
	 *
	 * @var string
	 */
	private $_loggersFactoryServiceId = 'loggersFactory';
	
	/**
	 * If hasn't initialized logger, try the loggers factory first
	 *
	 * @var bool
	 */
	private $_tryLoggersFactoryFirst = false;
	
	/**
	 * Log an info
	 * 
	 * @param string $message
	 */
	protected function logInfo($message) {
		$this->getLogger()->info($message);
	}
	
	/**
	 * Log a notice
	 * 
	 * @param string $message
	 */
	protected function logNotice($message) {
		$this->getLogger()->notice($message);
	}
	
	/**
	 * Log a warning
	 * 
	 * @param string $message
	 */
	protected function logWarning($message) {
		$this->getLogger()->warning($message);
	}
	
	/**
	 * Log an error
	 * 
	 * @param string $message
	 */
	protected function logError($message) {
		$this->getLogger()->error($message);
	}
	
	/**
	 * Log an alert
	 * 
	 * @param string $message
	 */
	protected function logAlert($message) {
		$this->getLogger()->alert($message);
	}
	
	/**
	 * Log an emergency
	 * 
	 * @param string $message
	 */
	protected function logEmergency($message) {
		$this->getLogger()->emergency($message);
	}

	/**
	 * Log a critical
	 * 
	 * @param string $message
	 */
	protected function logCritical($message) {
		$this->getLogger()->critical($message);
	}
	
	/**
	 * Log a debug
	 * 
	 * @param string $message
	 */
	protected function logDebug($message) {
		$this->getLogger()->debug($message);
	}
	
	/**
	 * Set a service id of a logger in services' locator
	 * 
	 * @param  string $id
	 */
	public function setLoggerServiceId($id) {
		$this->_loggerServiceId = (string) $id;
	}
	
	/**
	 * Get a service id of a logger in services' locator
	 * 
	 * @return string
	 */
	public function getLoggerServiceId() {
		return $this->_loggerServiceId;
	}
	
	/**
	 * Set a service id of a loggers' factory in services' locator
	 * 
	 * @param  string $id
	 */
	public function setLoggersFactoryServiceId($id) {
		$this->_loggersFactoryServiceId = (string) $id;
	}
	
	/**
	 * Get a service id of a loggers' factory in services' locator
	 * 
	 * @return string
	 */
	public function getLoggersFactoryServiceId() {
		return $this->_loggersFactoryServiceId;
	}
	
	/**
	 * Set behaviour: "If hasn't initialized logger, try the loggers factory first"
	 * 
	 * @param bool $flag
	 */
	public function setTryLoggersFactoryFirst($flag = true) {
		$this->_tryLoggersFactoryFirst = (bool) $flag;
	}
	
	/**
	 * Is "Try the loggers\' factory first" behaviour ?
	 * 
	 * @return bool
	 */
	public function isTryLoggersFactoryFirst() {
		return $this->_tryLoggersFactoryFirst;
	}
	
	/**
	 * Set a logger
	 * 
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger) {
		$this->_logger = $logger;
	}
	
	/**
	 * Get a logger
	 * 
	 * @return LoggerInterface
	 */
	public function getLogger() {
		if ($this->_logger !== null) {
			return $this->_logger;
		}
		
		$factoryFirst = $this->isTryLoggersFactoryFirst();
		
		if ($factoryFirst) {
			try {
				$logger = $this->_createLogger();
			} catch (Exception $exception) {}
		}
		
		if (empty($logger)) {
			try {
				$logger = $this->_getLogger();
			} catch (Exception $exception) {}
		}
		
		if (! $factoryFirst && empty($logger)) {
			try {
				$logger = $this->_createLogger();
			} catch (Exception $exception) {}
		}
		
		if (empty($logger)) {
			throw new NoLogger('Unable to provide a logger', 0, $exception);
		}
		
		if (! $logger instanceof LoggerInterface) {
			throw new NoLogger('Logger must implement the "LoggerInterface"');
		}
				
		$this->_logger = $logger;
		return $logger;
	}
	
	private function _getLogger() {
		if (! $this instanceof LocatorAwareInterface) {
			throw new NoLogger('Neither a service\'s locator nor logger has been specified');
		}
		
		$this->beforeLoggerInit();

		if ($this->hasLocator()) {
			$logger = $this->getLocator()->get($this->getLoggerServiceId());
		} else {
			throw new NoLogger('Neither a logger nor a loggers\' factory nor a locator has been specified');
		}

		$this->afterLoggerInit($logger);
		
		return $logger;
	}
	
	private function _createLogger() {
		if (method_exists($this, 'getServiceName')) {
			return $this->getLoggersFactory()->createForService($this->getServiceName());
		} else {
			throw new NoFactory('Unable to create logger due to unable to determine the service name');
		}
	}
	
	/**
	 * Set a loggers' factory
	 * 
	 * @param FactoryInterface $factory
	 */
	public function setLoggersFactory(FactoryInterface $factory) {
		$this->_loggersFactory = $factory;
	}
	
	/**
	 * Get a loggers' factory
	 * 
	 * @return FactoryInterface
	 * @throws NoFactory
	 */
	public function getLoggersFactory() {
		if ($this->_loggersFactory !== null) {
			return $this->_loggersFactory;
		}
		
		if (! $this instanceof LocatorAwareInterface) {
			throw new NoFactory('Neither a service\'s locator nor loggers\' factory has been specified');
		}

		if ($this->hasLocator()) {
			$this->_loggersFactory = $this->getLocator()->get($this->getLoggersFactoryServiceId());
			
			if (! $this->_loggersFactory instanceof FactoryInterface) {
				throw new NoFactory('Factory must implement the "LoggerInterface"');
			}
		} else {
			throw new NoFactory('Neither a logger\'s factory nor a locator has been specified');
		}
		
		return $this->_loggersFactory;
	}
	
	/**
	 * Calls before logger init start
	 */
	protected function beforeLoggerInit(){}
	
	/**
	 * Calls after logger init end
	 * 
	 * @param LoggerInterface $name Description
	 */
	protected function afterLoggerInit(){}
	
	/**
	 * Has a logger
	 * 
	 * @param  bool $considerFactory
	 * @return bool
	 */
	public function hasLogger($considerFactory = true) {
		if ($this->_logger !== null) {
			return true;
		}
		
		if ($this instanceof LocatorAwareInterface && $this->hasLocator()
			&& $this->getLocator()->has($this->getLoggerServiceId())) {
			return true;
		}
		
		return $considerFactory ? $this->hasLoggersFactory() : false;
	}
	
	/**
	 * Has a loggers' factory
	 * 
	 * @return bool
	 */
	public function hasLoggersFactory() {
		if ($this->_loggersFactory !== null) {
			return true;
		}
		
		if ($this instanceof LocatorAwareInterface && $this->hasLocator()
			&& $this->getLocator()->has($this->getLoggersFactoryServiceId())) {
			return true;
		}
		
		return false;
	}
	
}