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

use ZExt\Helper\Exceptions\NoHelpersBroker;
use ZExt\Di\LocatorAwareInterface;

/**
 * Helpers' broker trait
 * 
 * @category   ZExt
 * @package    Helper
 * @subpackage Broker
 * @author     Mike.Mirten
 * @version    1.0rc1
 */
trait HelpersBrokerAwareTrait {
	
	/**
	 * Helper broker
	 *
	 * @var HelperBrokerInterface
	 */
	private $_helperBroker;
	
	/**
	 * Id of a helper's broker service in service's locator
	 *
	 * @var string
	 */
	protected $_helperBrokerServiceId = 'defaultHelpersBroker';
	
	/**
	 * Set a service id of a helper's broker in service's locator
	 * 
	 * @param  string $id
	 * @return HelpersBrokerAwareTrait
	 */
	public function setHelpersBrokerServiceId($id) {
		$this->_helperBrokerServiceId = $id;
				
		return $this;
	}
	
	/**
	 * Get a service id of a helper's broker in service's locator
	 * 
	 * @return string
	 */
	public function getHelpersBrokerServiceId() {
		return $this->_helperBrokerServiceId;
	}
	
	/**
	 * Set a helper's broker
	 * 
	 * @param  HelpersLocatorInterface $helperBroker
	 * @return HelpersBrokerAwareTrait
	 */
	public function setHelpersBroker(HelpersLocatorInterface $helperBroker) {
		$this->_helperBroker = $helperBroker;
		
		return $this;
	}
	
	/**
	 * Get a helper's broker
	 * 
	 * @return HelpersLocatorInterface
	 */
	public function getHelpersBroker() {
		if ($this->_helperBroker === null) {
			if ($this instanceof LocatorAwareInterface && $this->hasLocator()) {
				$this->_helperBroker = $this->getLocator()->get($this->getHelpersBrokerServiceId());
				
				if (! $this->_helperBroker instanceof HelpersLocatorInterface) {
					throw new NoHelpersBroker('Helper\'s broker must implement the "HelpersLocatorInterface"');
				}
			} else {
				throw new NoHelpersBroker('No helper\'s broker been supplied, also unable to obtain one through locator');
			}
		}
		
		return $this->_helperBroker;
	}
	
	/**
	 * Has a helper broker
	 * 
	 * @return bool
	 */
	public function hasHelpersBroker() {
		if ($this->_helperBroker !== null) {
			return true;
		}
		
		if ($this instanceof LocatorAwareInterface && $this->hasLocator()
			&& $this->getLocator()->has($this->getHelpersBrokerServiceId())) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Call a helper
	 * 
	 * @param  string $method
	 * @param  array  $args
	 * @return mixed
	 */
	public function __call($method, $args) {
		$helper = $this->getHelpersBroker()->get($method);
		
		if (! $helper->hasParent()) {
			$helper->setParent($this);
		}
		
		return call_user_func_array([$helper, $method], $args);
	}
	
}