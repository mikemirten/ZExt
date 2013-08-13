<?php
namespace ZExt\Profiler;

interface ProfilerInterface {
	
	/**
	 * Start an event
	 * 
	 * @param string $title
	 * @param array $options
	 * @return ProfileInterface
	 */
	public function startEvent($message, $type = ProfileInterface::TYPE_INFO, array $options = null);
	
	/**
	 * Stop a last event
	 * 
	 * @return Profiler
	 * @throws Exception
	 */
	public function stopEvent($status = ProfileInterface::STATUS_SUCCESS);
	
	/**
	 * Get profiler's results
	 * 
	 * @return Profile[]
	 */
	public function getProfiles();
	
	/**
	 * Get the total elapsed time of an events in seconds
	 * 
	 * @return int
	 */
	public function getTotalElapsedTime();
	
	/**
	 * Get total events has occurred
	 * 
	 * @return int
	 */
	public function getTotalEvents();
	
}