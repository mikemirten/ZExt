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

namespace ZExt\Cli\Phalcon;

use Phalcon\CLI\Task;
use ReflectionObject, ReflectionMethod;

use ZExt\Di\LocatorAwareInterface,
    ZExt\Di\LocatorInterface;

use ZExt\Helper\HelpersBrokerAwareInterface,
    ZExt\Helper\HelpersBrokerAwareTrait;

/**
 * Abstract Phalcon based task
 * 
 * @category   ZExt
 * @package    Cli
 * @subpackage Task
 * @author     Mike.Mirten
 * @version    1.1
 */
abstract class TaskAbstract extends Task implements HelpersBrokerAwareInterface, LocatorAwareInterface {
	
	use HelpersBrokerAwareTrait;
	
	/**
	 * Services locator
	 *
	 * @var LocatorInterface
	 */
	private $_locator;
	
	/**
	 * Default main action of a task
	 */
	public function mainAction() {
		$this->actionslistAction();
	}
	
	/**
	 * Show the list of a task's actions
	 */
	final public function actionslistAction() {
		$reflection = new ReflectionObject($this);
		
		echo "Available actions:\033[1;32m";
		
		foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
			$length = strlen($method->name) - 6;
			
			if (strpos($method->name, 'Action') === $length) {
				echo ' ', substr($method->name, 0, $length);
			}
		}
		
		echo "\033[0m";
	}
	
	/**
	 * Get a service id of a helper's broker in service's locator
	 * 
	 * @return string
	 */
	protected function getHelpersBrokerServiceId() {
		return 'cliHelpersBroker';
	}
	
	/**
	 * Set a services locator
	 * 
	 * @param LocatorInterface $locator
	 */
	public function setLocator(LocatorInterface $locator) {
		$this->_locator = $locator;
	}

	/**
	 * Get a services' locator
	 * 
	 * @return LocatorInterface
	 */
	public function getLocator() {
		if ($this->_locator === null) {
			$di = $this->getDi();
		
			if (! $di instanceof LocatorInterface) {
				throw new NoLocator('Hasn\'t been locator provided');
			}
			
			$this->_locator = $di;
		}
		
		return $this->_locator;
	}
	
	/**
	 * Has a services' locator
	 * 
	 * @return boolean
	 */
	public function hasLocator() {
		return $this->_locator !== null || $this->getDi() !== null;
	}
	
	/**
	 * Get the parameter from the request
	 * 
	 * @param  string         $name    Parameter name
	 * @param  mixed          $default Parameter default value
	 * @param  string | array $filters Value's filter(s)
	 * @return mixed
	 */
	protected function getParam($name, $default = null, $filters = null) {
		return $this->getDi()->get('dispatcher')->getParam($name, $filters, $default);
	}
	
	/**
	 * Get all parameters from the request
	 * 
	 * @param  array $defaults Default parameters' values
	 * @return array
	 */
	protected function getParams(array $defaults = []) {
		return array_replace(
			$defaults,
			$this->getDi()->get('dispatcher')->getParams()
		);
	}
	
	/**
	 * Has the parameter from the request
	 * 
	 * @param  string $name
	 * @return bool
	 */
	protected function hasParam($name) {
		if ($this->getDi()->get('dispatcher')->getParam($name, null, false)) {
			return true;
		}
		
		return false;
	}
	
}