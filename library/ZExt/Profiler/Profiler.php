<?php
namespace ZExt\Profiler;

class Profiler implements ProfilerInterface, ProfilerExtendedInterface {
	
	/**
	 * Profiles
	 * 
	 * @var ProfileInterface[] 
	 */
	protected $_profiles = array();
	
	/**
	 * Name of a profiling object
	 * 
	 * @var string
	 */
	protected $_name;
	
	/**
	 * Icon of a profiling object
	 *
	 * @var string
	 */
	protected $_icon;
	
	/**
	 * Additional info about a profiling object
	 * 
	 * @var string | array
	 */
	protected $_info;
	
	/**
	 * Last started profile
	 *
	 * @var ProfileInterface
	 */
	protected $_lastProfile;
	
	/**
	 * Start an event
	 * 
	 * @param  string $title
	 * @param  array $options
	 * @return ProfileInterface
	 */
	public function startEvent($message, $type = ProfileInterface::TYPE_INFO, array $options = null) {
		$profile = new Profile($message, $type, $options);
		
		$this->_profiles[]  = $profile;
		$this->_lastProfile = $profile;
		
		$profile->start();
		
		return $profile;
	}
	
	/**
	 * Stop a last event
	 * 
	 * @return Profiler
	 * @throws Exception
	 */
	public function stopEvent($status = ProfileInterface::STATUS_SUCCESS) {
		if ($this->_lastProfile === null) {
			throw new Exception('No one event hasn\'t been start');
		}
		
		$this->_lastProfile->stop($status);
		
		return $this;
	}
	
	/**
	 * Get profiles
	 * 
	 * @return ProfileInterface[]
	 */
	public function getProfiles() {
		return $this->_profiles;
	}
	
	/**
	 * Get the total elapsed time of an events
	 * 
	 * @return int
	 */
	public function getTotalElapsedTime() {
		$time = 0;
		
		foreach ($this->_profiles as $profile) {
			$time += $profile->getElapsedTime();
		}
		
		return $time;
	}
	
	/**
	 * Get total events has occurred
	 * 
	 * @return int
	 */
	public function getTotalEvents() {
		return count($this->_profiles);
	}
	
	/**
	 * Get icon of a profiling object
	 * 
	 * @return string
	 */
	public function getIcon() {
		return $this->_icon;
	}
	
	/**
	 * Get icon of a profiling object
	 * 
	 * @param  string $image
	 * @return Profiler
	 */
	public function setIcon($image) {
		$this->_icon = (string) $image;
		
		return $this;
	}
	
	/**
	 * Get a name of a profiling object
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * Set a name of a profiling object
	 * 
	 * @param  string $name
	 * @return Profiler
	 */
	public function setName($name) {
		$this->_name = (string) $name;
		
		return $this;
	}
	
	/**
	 * Get an additional info about a profiling object
	 * 
	 * @return string | array
	 */
	public function getAdditionalInfo() {
		return $this->_info;
	}
	
	/**
	 * Set an additional info about a profiling object
	 * 
	 * @param  string | array $info
	 * @return Profiler
	 */
	public function setAdditionalInfo($info) {
		$this->_info = $info;
		
		return $this;
	}
	
	/**
	 * Render results as a text
	 * 
	 * @return string
	 */
	public function render() {
		$totalEvents = $this->getTotalEvents();
		
		if ($totalEvents > 0) {
			$text  = 'Total: ' . $totalEvents . ' events in: ';
			$text .= round($this->getTotalElapsedTime(), 4) . 's ';
		} else {
			$text = 'No events';
		}
		
		$text .= PHP_EOL;
		
		$info = $this->getAdditionalInfo();
		
		if ($info !== null) {
			$text .= PHP_EOL;
			$text .= 'Info:' . PHP_EOL;
			
			if (is_string($info)) {
				$text .= $info . PHP_EOL;
			} else if (is_array($info)) {
				foreach ($info as $key => $part) {
					$text .= $key . ': ' . $part . PHP_EOL;
				}
			}
		}
		
		if ($totalEvents > 0) {
			$profiles = $this->getProfiles();
			$nm       = 1;
			
			$text .= PHP_EOL;
			$text .= 'Events:' . PHP_EOL;
			
			foreach ($profiles as $profile) {
				$text .= $nm++ . '. ';
				$text .= $profile->getMessage() . ':';
				$text .= ' ' . round($profile->getElapsedTime(), 4) . 's';
				$text .= ' ' . $profile->getUsedMemory() . 'b';
				$text .= PHP_EOL;
			}
		}
		
		return $text;
	}
	
	public function __toString() {
		return $this->render();
	}
	
}