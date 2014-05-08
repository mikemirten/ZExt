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

namespace ZExt\Helper;

use ZExt\Di\LocatorAwareInterface,
    ZExt\Di\LocatorAwareTrait,
    ZExt\Di\LocatorInterface;

use ZExt\Helper\Exceptions\NoNamespaces,
    ZExt\Helper\Exceptions\WrongHelper,
    ZExt\Helper\Exceptions\NoHelper;

use Closure;

/**
 * Namespaces based helpers loader
 * 
 * @category   ZExt
 * @package    Helper
 * @subpackage Loader
 * @author     Mike.Mirten
 * @version    1.1
 */
class LoaderNamespace implements LoaderInterface, HelpersLocatorInterface, LocatorAwareInterface {
	
	use LocatorAwareTrait;
	
	/**
	 * Helpers
	 *
	 * @var HelperInterface[]
	 */
	protected $_helpers = [];
	
	/**
	 * Namespaces
	 *
	 * @var string[]
	 */
	protected $_namespaces = [];
	
	/**
	 * On init callback
	 * 
	 * @var Closure
	 */
	protected $_onInit;
	
	/**
	 * Misses while class loading
	 *
	 * @var int
	 */
	protected $_loadMisses = 0;
	
	/**
	 * Constructor
	 * 
	 * @param LocatorInterface $locator
	 * @param string | array   $namespace
	 */
	public function __construct(LocatorInterface $locator = null, $namespace = null) {
		if ($locator !== null) {
			$this->setLocator($locator);
		}
		
		if ($namespace !== null) {
			if (is_array($namespace)) {
				$this->registerNamespaces($namespace);
			} else {
				$this->registerNamespace($namespace);
			}
		}
	}
	
	/**
	 * Register the helpers' namespaces
	 * 
	 * @param  array $namespaces
	 * @return LoaderNamespace
	 */
	public function registerNamespaces(array $namespaces) {
		foreach ($namespaces as $namespace) {
			$this->registerNamespace($namespace);
		}
		
		return $this;
	}
	
	/**
	 * Register the helpers' namespace
	 * 
	 * @param  string $namespace
	 * @return LoaderNamespace
	 */
	public function registerNamespace($namespace) {
		if (! in_array($namespace, $this->_namespaces, true)) {
			$this->_namespaces[] = (string) $namespace;
		}
		
		return $this;
	}
	
	/**
	 * Get the registered namespaces
	 * 
	 * @return array
	 */
	public function getNamespaces() {
		return $this->_namespaces;
	}
	
	/**
	 * Calls right after an object instantiation
	 * 
	 * @param  Closure $callback
	 * @return LoaderNamespace
	 */
	public function setOnInit(Closure $callback) {
		$this->_onInit = $callback;
		
		return $this;
	}
	
	/**
	 * Load the helper
	 * 
	 * @param  string $id
	 * @return HelperInterface | null
	 */
	public function load($id) {
		if (isset($this->_helpers[$id])) {
			return $this->_helpers[$id];
		}
		
		$helper = $this->loadHelper($id);
		
		if ($helper === null) {
			return;
		}
		
		if ($this->_onInit !== null) {
			$this->_onInit->__invoke($helper);
		}
		
		if ($helper instanceof LocatorAwareInterface && ! $helper->hasLocator() && $this->hasLocator()) {
			$helper->setLocator($this->getLocator());
		}
		
		$this->_helpers[$id] = $helper;
		
		return $helper;
	}
	
	/**
	 * Load the service
	 * 
	 * @param  string $id
	 * @return HelperInterface | bool
	 * @throws NoNamespaces
	 * @throws WrongHelper
	 */
	protected function loadHelper($id, $onlyCheck = false) {
		$namespaces = $this->getNamespaces();
		
		if (empty($namespaces)) {
			throw new NoNamespaces('Wasn\'t a namespaces registered');
		}
		
		$misses = &$this->_loadMisses;
		set_error_handler(function() use(&$misses) {
			++ $misses;
		});
		
		foreach ($this->_namespaces as $namespace) {
			$class = $namespace . '\\' . ucfirst($id);
			
			if (class_exists($class)) {
				restore_error_handler();
				unset($misses);
				
				if ($onlyCheck) {
					return true;
				} else {
					$helper = new $class();
					
					if (! $helper instanceof HelperInterface) {
						throw new WrongHelper('Helper must implement the "HelperInterface"');
					}
					
					return $helper;
				}
			}
		}
		
		restore_error_handler();
		unset($misses);
	}

	/**
	 * Get a misses number which were occurred while a class loading
	 * 
	 * @return int
	 */
	public function getMisses() {
		return $this->_loadMisses;
	}
	
	/**
	 * Get the helper
	 * 
	 * @param  string $id
	 * @return HelperInterface
	 * @throws NoHelper
	 */
	public function get($id) {
		if (isset($this->_helpers[$id])) {
			return $this->_helpers[$id];
		}
		
		$helper = $this->load($id);
		
		if ($helper === null) {
			$namespaces = '"' . implode('", "', $this->getNamespaces()) . '"';
			throw new NoHelper('Unable to load helper "' . $id . '". Registered namespaces: ' . $namespaces);
		}
		
		if ($helper instanceof LocatorAwareInterface && ! $helper->hasLocator() && $this->hasLocator()) {
			$helper->setLocator($this->getLocator());
		}
		
		if ($helper instanceof HelpersBrokerAwareInterface && ! $helper->hasHelpersBroker()) {
			$helper->setHelpersBroker($this);
		}
		
		$this->_helpers[$id] = $helper;
		
		return $helper;
	}
	
	/**
	 * Has the helper
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function has($id) {
		if (isset($this->_helpers[$id])) {
			return true;
		}
		
		return $this->loadHelper($id, true);
	}
	
	/**
	 * Get the helper
	 * 
	 * @param  string $name
	 * @return HelperInterface
	 */
	public function __get($name) {
		return $this->load($name);
	}
	
	/**
	 * Has the helper
	 * 
	 * @param  string $name
	 * @return bool
	 */
	public function __isset($name) {
		return $this->loadHelper($name, true);
	}
	
	/**
	 * Call the helper
	 * 
	 * @param  string $method
	 * @param  array  $args
	 * @return mixed
	 */
	public function __call($method, $args) {
		$helper = $this->get($method);

		return call_user_func_array([$helper, $method], $args);
	}
	
}