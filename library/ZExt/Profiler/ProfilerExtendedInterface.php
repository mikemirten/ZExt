<?php
namespace ZExt\Profiler;

interface ProfilerExtendedInterface {
	
	/**
	 * Get icon of a profiling object
	 * 
	 * @return string
	 */
	public function getIcon();
	
	/**
	 * Get icon of a profiling object
	 * 
	 * @param string $image
	 */
	public function setIcon($image);
	
	/**
	 * Get a name of a profiling object
	 * 
	 * @return string
	 */
	public function getName();
	
	/**
	 * Set a name of a profiling object
	 * 
	 * @param string $name
	 */
	public function setName($name);
	
	/**
	 * Get an additional info about a profiling object
	 * 
	 * @return string | array
	 */
	public function getAdditionalInfo();
	
	/**
	 * Set an additional info about a profiling object
	 * 
	 * @param string $info
	 */
	public function setAdditionalInfo($info);
	
}