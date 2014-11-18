<?php
namespace ZExt\Filesystem\Exceptions;

use ZExt\Exceptions\ExceptionAbstract;

class InvalidPath extends ExceptionAbstract {
	
	/**
	 * Path to file
	 *
	 * @var string
	 */
	protected $path;
	
	/**
	 * Constructor
	 * 
	 * @param string     $message
	 * @param int        $code
	 * @param \Exception $previous
	 * @param string     $path
	 */
	public function __construct($message, $code, $previous, $path) {
		parent::__construct($message, $code, $previous);
		
		$this->path = $path;
	}
	
	/**
	 * Get path to file
	 * 
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}
	
}