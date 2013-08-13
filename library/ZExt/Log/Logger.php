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

use ZExt\Di\LocatorAwareInterface,
    ZExt\Di\LocatorAwareTrait;

use ZExt\Events\EventsManagerAwareInterface,
    ZExt\Events\EventsManagerAwareTrait;

use ZExt\Log\Adapters\AdapterInterface;

use ZExt\Log\Exceptions\NoAdapter;

/**
 * Logger
 * 
 * @category   ZExt
 * @package    Logger
 * @subpackage Logger
 * @author     Mike.Mirten
 * @version    1.0rc1
 */
class Logger implements LoggerInterface, LocatorAwareInterface, EventsManagerAwareInterface {
	
	use LocatorAwareTrait;
	use EventsManagerAwareTrait;
	
	const DEFAULT_LOGGER_ADAPTER = 'defaultLoggerAdapter';
	
	/**
	 * Logger's adapter
	 *
	 * @var AdapterInterface
	 */
	private $_adapter;
	
	/**
	 * Constructor
	 * 
	 * @param AdapterInterface $adapter
	 */
	public function __construct(AdapterInterface $adapter = null) {
		if ($adapter !== null) {
			$this->setAdapter($adapter);
		}
	}
	
	/**
	 * Log an info
	 * 
	 * @param  string $message
	 * @return Logger
	 */
	public function info($message) {
		$this->getAdapter()->log($message, self::TYPE_INFO);
		
		return $this;
	}
	
	/**
	 * Log a notice
	 * 
	 * @param  string $message
	 * @return Logger
	 */
	public function notice($message) {
		$this->getAdapter()->log($message, self::TYPE_NOTICE);
		
		return $this;
	}
	
	/**
	 * Log a warning
	 * 
	 * @param  string $message
	 * @return Logger
	 */
	public function warning($message) {
		$this->getAdapter()->log($message, self::TYPE_WARNING);
		
		return $this;
	}
	
	/**
	 * Log an error
	 * 
	 * @param  string $message
	 * @return Logger
	 */
	public function error($message) {
		$this->getAdapter()->log($message, self::TYPE_ERROR);
		
		return $this;
	}
	
	/**
	 * Log an alert
	 * 
	 * @param  string $message
	 * @return Logger
	 */
	public function alert($message) {
		$this->getAdapter()->log($message, self::TYPE_ALERT);
		
		return $this;
	}
	
	/**
	 * Log an emergency
	 * 
	 * @param  string $message
	 * @return Logger
	 */
	public function emergency($message) {
		$this->getAdapter()->log($message, self::TYPE_EMERGENCY);
		
		return $this;
	}

	/**
	 * Log a critical
	 * 
	 * @param  string $message
	 * @return Logger
	 */
	public function critical($message) {
		$this->getAdapter()->log($message, self::TYPE_CRITICAL);
		
		return $this;
	}
	
	/**
	 * Log a debug
	 * 
	 * @param  string $message
	 * @return Logger
	 */
	public function debug($message) {
		$this->getAdapter()->log($message, self::TYPE_DEBUG);
		
		return $this;
	}
	
	/**
	 * Set a logger's adapter
	 * 
	 * @param  AdapterInterface $adapter
	 * @return Logger
	 */
	public function setAdapter(AdapterInterface $adapter) {
		$this->_adapter = $adapter;
		
		return $this;
	}
	
	/**
	 * Get a logger's adapter
	 * 
	 * @param AdapterInterface $adapter
	 */
	public function getAdapter() {
		if ($this->_adapter === null) {
			$this->_adapter = $this->getLocator()->get(self::DEFAULT_LOGGER_ADAPTER);

			if (! $this->_adapter instanceof AdapterInterface) {
				throw new NoAdapter('Adapter must implement the "AdapterInterface"');
			}
		}
		
		return $this->_adapter;
	}
	
}