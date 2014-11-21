<?php

namespace ZExt\Di\Config\Exceptions;

use ZExt\Di\Exceptions\InvalidConfig;
use ZExt\Xml\Element;

class InvalidConfigXml extends InvalidConfig {
	
	/**
	 * Element of XML configuration caused the failure
	 *
	 * @var Element 
	 */
	protected $element;
	
	/**
	 * Constructor
	 * 
	 * @param string     $message
	 * @param int        $code
	 * @param \Exception $previous
	 * @param Element    $element Element of XML configuration caused the failure
	 */
	public function __construct($message, $code, $previous, Element $element = null) {
		parent::__construct($message, $code, $previous);
		
		$this->element = $element;
	}
	
	/**
	 * Get the fragment caused the failure
	 * 
	 * @return string
	 */
	public function getFragment() {
		return $this->element->toXML();
	}
	
}