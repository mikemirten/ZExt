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

use ZExt\Helper\Exceptions\NoHelper,
    ZExt\Helper\Exceptions\NoLoaders;


/**
 * Helpers broker
 * 
 * @category   ZExt
 * @package    Helper
 * @subpackage Broker
 * @author     Mike.Mirten
 * @version    1.0rc1
 */
class HelpersBroker implements HelpersBrokerInterface, LocatorAwareInterface {
	
	use LocatorAwareTrait;
	
	/**
	 * Loaders
	 *
	 * @var LoaderInterface[]
	 */
	protected $_loaders = [];
	
	/**
	 * Helpers
	 *
	 * @var HelperInterface[]
	 */
	protected $_helpers = [];
	
	/**
	 * Constructor
	 * 
	 * @param LocatorInterface $locator
	 * @param LoaderInterface  $loader
	 */
	public function __construct(LocatorInterface $locator = null, LoaderInterface $loader = null) {
		if ($locator !== null) {
			$this->setLocator($locator);
		}
		
		if ($loader !== null) {
			$this->registerLoader($loader);
		}
	}
	
	/**
	 * Get the helper
	 * 
	 * @param  string $id
	 * @return HelperInterface
	 */
	public function get($id) {
		if (isset($this->_helpers[$id])) {
			return $this->_helpers[$id];
		}
		
		$helper = $this->loadHelper($id);
		
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
	 * Set the helper
	 * 
	 * @param  string          $id
	 * @param  HelperInterface $helper
	 * @return HelperBroker
	 */
	public function set($id, HelperInterface $helper) {
		$this->_helpers[$id] = $helper;
		
		return $this;
	}
	
	/**
	 * Has the helper
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function has($id) {
		return isset($this->_helpers[$id]);
	}
	
	/**
	 * Register the loader
	 * 
	 * @param  LoaderInterface $loader
	 * @param  string $id
	 * @return HelpersBroker
	 */
	public function registerLoader(LoaderInterface $loader, $id = null) {
		if ($id === null) {
			$this->_loaders[] = $loader;
		} else if (! isset($this->_loaders[$id])) {
			$this->_loaders[$id] = $loader;
		}
		
		if ($loader instanceof LocatorAwareInterface && ! $loader->hasLocator() && $this->hasLocator()) {
			$loader->setLocator($this->getLocator());
		}
		
		return $this;
	}
	
	/**
	 * Load the helper
	 * 
	 * @param  string $id
	 * @return HelperInterface
	 * @throws NoLoaders
	 * @throws NoHelper
	 */
	public function loadHelper($id) {
		if (empty($this->_loaders)) {
			throw new NoLoaders('Wasn\'t a loaders registered');
		}
		
		foreach ($this->_loaders as $loader) {
			$helper = $loader->load($id);
			
			if ($helper !== null) {
				return $helper;
			}
		}
		
		throw new NoHelper('Unable to load helper "' . $id . '"');
	}
	
	/**
	 * Get the helper
	 * 
	 * @param  string $name
	 * @return HelperInterface
	 */
	public function __get($name) {
		return $this->get($name);
	}
	
	/**
	 * Set the helper
	 * 
	 * @param  string          $name
	 * @param  HelperInterface $helper
	 */
	public function __set($name, $value) {
		$this->set($name, $value);
	}
	
	/**
	 * Has the helper
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function __isset($name) {
		return $this->has($name);
	}
	
}