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

namespace ZExt\Config;

/**
 * Configuration holder aware interface
 * 
 * @category   ZExt
 * @package    Config
 * @subpackage ConfigAware
 * @author     Mike.Mirten
 * @version    1.0
 */
interface ConfigAwareInterface {
	
	/**
	 * Set a service id of the config
	 * 
	 * @param string $id
	 */
	public function setConfigServiceId($id);
	
	/**
	 * Get a service id of the config
	 * 
	 * @return string
	 */
	public function getConfigServiceId();
	
	/**
	 * Set a config
	 * 
	 * @param ConfigInterface $config
	 */
	public function setConfig(ConfigInterface $config);
	
	/**
	 * Get a config
	 * 
	 * @return ConfigInterface
	 */
	public function getConfig();
	
	/**
	 * Has a config
	 * 
	 * @return bool
	 */
	public function hasConfig();
	
	/**
	 * Set a configs' factory
	 * 
	 * @param FactoryInterface $factory
	 */
	public function setConfigsFactory(FactoryInterface $factory);
	
	/**
	 * Get a configs' factory
	 * 
	 * @return FactoryInterface
	 */
	public function getConfigsFactory();
	
	/**
	 * Has a configs' factory
	 * 
	 * @return bool
	 */
	public function hasConfigsFactory();
	
}