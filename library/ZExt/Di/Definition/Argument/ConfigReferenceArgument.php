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

use ZExt\Config\ConfigAwareInterface,
    ZExt\Config\ConfigAwareTrait,
    ZExt\Config\ConfigInterface;

/**
 * Service reference argument for definition
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Definition
 * @author     Mike.Mirten
 * @version    1.0
 */
class ConfigReferenceArgument implements ArgumentInterface, ConfigAwareInterface {
	
	use ConfigAwareTrait;
	
	/**
	 * Parameter's name
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 * Constructor
	 * 
	 * @param ConfigInterface $config Config with parameteres
	 * @param string          $name   Name of parameter
	 */
	public function __construct(ConfigInterface $config, $name) {
		$this->setConfig($config);
		
		$this->name = $name;
	}
	
	/**
	 * Get argument's value
	 * 
	 * @return mixed
	 */
	public function getValue() {
		return $this->getConfig()->get($this->name);
	}
	
}