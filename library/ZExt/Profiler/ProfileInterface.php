<?php
namespace ZExt\Profiler;

interface ProfileInterface {
	
	// Base types of events
	const TYPE_INFO   = 1;
	const TYPE_READ   = 2;
	const TYPE_WRITE  = 3;
	const TYPE_INSERT = 4;
	const TYPE_DELETE = 5;
	const TYPE_INC    = 6;
	const TYPE_DEC    = 7;
	const TYPE_COUNT  = 8;
	
	// Base types of events' statuses
	const STATUS_SUCCESS = 1;
	const STATUS_NOTICE  = 2;
	const STATUS_WARNING = 3;
	const STATUS_ERROR   = 4;
	
	/**
	 * Constructor
	 * 
	 * @param string $message
	 * @param int    $type
	 * @param array  $options
	 */
	public function __construct($message, $type = self::TYPE_INFO, array $options = null);
	
	/**
	 * Start an event
	 * 
	 * @param int $type Type of an event
	 */
	public function start();
	
	/**
	 * Stop an event
	 * 
	 * @param int $type Status of event end
	 */
	public function stop($type = self::STATUS_SUCCESS);
	
	/**
	 * Has an event ended
	 * 
	 * @return bool
	 */
	public function hasEnded();
	
	/**
	 * Get elapsed time of an event in seconds
	 * 
	 * @return float
	 */
	public function getElapsedTime();
	
	/**
	 * Get used memory of an event
	 * 
	 * @return int
	 */
	public function getUsedMemory();
	
	/**
	 * Get message of an event
	 * 
	 * @return string
	 */
	public function getMessage();
	
	/**
	 * Get type of an event
	 * 
	 * @return int
	 */
	public function getType();
	
	/**
	 * Get status of end of an event
	 * 
	 * @return int
	 */
	public function getStatus();
	
}