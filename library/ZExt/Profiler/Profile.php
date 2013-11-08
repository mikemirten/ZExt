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

namespace ZExt\Profiler;


/**
 * Profile
 * 
 * @category   ZExt
 * @package    Profiler
 * @subpackage Profile
 * @author     Mike.Mirten
 * @version    1.1
 */
class Profile implements ProfileInterface {
	
	/**
	 * Time at the event start
	 *
	 * @var int
	 */
	protected $_startTime;
	
	/**
	 * Time at the event stop
	 *
	 * @var int
	 */
	protected $_stopTime;
	
	/**
	 * Memory usage at the event start
	 *
	 * @var int
	 */
	protected $_startMemory;
	
	/**
	 * Memory usage at the event stop
	 *
	 * @var int
	 */
	protected $_stopMemory;
	
	/**
	 * Event's message
	 *
	 * @var string
	 */
	protected $_message;
	
	/**
	 * Is the event started
	 *
	 * @var bool 
	 */
	protected $_started = false;
	
	/**
	 * Is the event stopped
	 *
	 * @var bool 
	 */
	protected $_ended = false;
	
	/**
	 * Event's options
	 *
	 * @var array | null
	 */
	protected $_options;
	
	/**
	 * Event's type
	 *
	 * @var int
	 */
	protected $_type;
	
	/**
	 * Event's end status
	 *
	 * @var int
	 */
	protected $_status;
	
	/**
	 * Constructor
	 * 
	 * @param string $message
	 * @param array  $options
	 */
	public function __construct($message, $type = self::TYPE_INFO, array $options = null) {
		if ($options !== null) {
			$this->_options = $options;
		}
		
		$this->_message = (string) $message;
		$this->_type    = (int)    $type;
	}
	
	/**
	 * Start the event
	 */
	public function start() {
		if ($this->_started === true) {
			throw new Exception('The event already been started');
		}
		
		$this->_startTime   = microtime(true);
		$this->_startMemory = memory_get_usage();
		$this->_started     = true;
	}
	
	/**
	 * Stop the event
	 */
	public function stop($type = self::STATUS_SUCCESS) {
		if ($this->_started === false) {
			throw new Exception('Unable to stop the event which has not been started');
		}
		
		if ($this->_ended === true) {
			throw new Exception('The event already been stopped');
		}
		
		$this->_stopTime   = microtime(true);
		$this->_stopMemory = memory_get_usage();
		$this->_status     = $type;
		$this->_stoped = true;
	}
	
	/**
	 * Stop the event with the success status
	 */
	public function stopSuccess() {
		$this->stop(self::STATUS_SUCCESS);
	}
	
	/**
	 * Stop the event with the notice status
	 */
	public function stopNotice() {
		$this->stop(self::STATUS_NOTICE);
	}
	
	/**
	 * Stop the event with the warning status
	 */
	public function stopWarning() {
		$this->stop(self::STATUS_WARNING);
	}
	
	/**
	 * Stop the event with the error status
	 */
	public function stopError() {
		$this->stop(self::STATUS_ERROR);
	}
	
	/**
	 * Has an event ended
	 * 
	 * @return bool
	 */
	public function hasEnded() {
		return $this->_ended;
	}
	
	/**
	 * Get elapsed time of an event in seconds
	 * 
	 * @return float
	 */
	public function getElapsedTime() {
		return $this->_stopTime - $this->_startTime;
	}
	
	/**
	 * Get used memory of an event
	 * 
	 * @return int
	 */
	public function getUsedMemory() {
		return $this->_stopMemory - $this->_startMemory;
	}
	
	/**
	 * Get message of an event
	 * 
	 * @return string
	 */
	public function getMessage() {
		return $this->_message;
	}
	
	/**
	 * Get type of an event
	 * 
	 * @return int
	 */
	public function getType() {
		return $this->_type;
	}
	
	/**
	 * Get status of end of an event
	 * 
	 * @return int
	 */
	public function getStatus() {
		return $this->_status;
	}
	
}