<?php
namespace ZExt\Profiler;

class Profile implements ProfileInterface {
	
	protected $_startTime;
	
	protected $_stopTime;
	
	protected $_startMemory;
	
	protected $_stopMemory;
	
	protected $_message;
	
	protected $_started = false;
	
	protected $_ended = false;
	
	protected $_options;
	
	protected $_type;
	
	protected $_status;
	
	/**
	 * Constructor
	 * 
	 * @param string $message
	 * @param array  $options
	 */
	public function __construct($message, $type = self::TYPE_INFO, array $options = null) {
		if ($options) $this->_options = $options;
		$this->_message = $message;
		$this->_type    = $type;
	}
	
	/**
	 * Start an event
	 */
	public function start() {
		if ($this->_started === true || $this->_ended === true) return;
		
		$this->_startTime   = microtime(true);
		$this->_startMemory = memory_get_usage();
	}
	
	/**
	 * Stop an event
	 */
	public function stop($type = self::STATUS_SUCCESS) {
		if ($this->_ended === true) return;
		
		$this->_stopTime   = microtime(true);
		$this->_stopMemory = memory_get_usage();
		$this->_status     = $type;
		
		$this->_stoped = true;
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