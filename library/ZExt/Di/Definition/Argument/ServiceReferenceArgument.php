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

namespace ZExt\Di\Definition\Argument;

use ZExt\Di\LocatorAwareInterface,
    ZExt\Di\LocatorAwareTrait,
    ZExt\Di\LocatorInterface;

/**
 * Service reference argument for definition
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Definition
 * @author     Mike.Mirten
 * @version    1.0
 */
class ServiceReferenceArgument implements ArgumentInterface, LocatorAwareInterface {
	
	use LocatorAwareTrait;
	
	/**
	 * Service ID
	 *
	 * @var string
	 */
	protected $id;
	
	/**
	 * Arguments for constructor of service
	 *
	 * @var mixed 
	 */
	protected $arguments;
	
	/**
	 * Constructor
	 * 
	 * @param LocatorInterface $locator   Service's locator
	 * @param string           $serviceId ID of required service
	 * @param mixed            $args      Arguments for required service if need
	 */
	public function __construct(LocatorInterface $locator, $serviceId, $args = null) {
		$this->id = $serviceId;
		
		$this->setArguments($args);
		$this->setLocator($locator);
	}
	
	/**
	 * Set arguments for constructor of service
	 * 
	 * @param mixed $args
	 */
	public function setArguments($args) {
		$this->arguments = $args;
	}
	
	/**
	 * Get arguments for constructor of service
	 * 
	 * @return mixed
	 */
	public function getArguments() {
		return $this->arguments;
	}
	
	/**
	 * Get argument's value
	 * 
	 * @return mixed
	 */
	public function getValue() {
		return $this->getLocator()->get($this->id, $this->arguments);
	}
	
}