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

namespace ZExt\Log;

/**
 * Logger aware interface
 * 
 * @category   ZExt
 * @package    Logger
 * @subpackage Logger
 * @author     Mike.Mirten
 * @version    1.0
 */
interface LoggerAwareInterface {
	
	/**
	 * Set a logger
	 * 
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger);
	
	/**
	 * Set a loggers' factory
	 * 
	 * @param FactoryInterface $factory
	 */
	public function setLoggersFactory(FactoryInterface $factory);
	
	/**
	 * Get a logger
	 * 
	 * @return LoggerInterface
	 */
	public function getLogger();
	
	/**
	 * Get a loggers' factory
	 * 
	 * @return FactoryInterface
	 */
	public function getLoggersFactory();
	
	/**
	 * Has a logger
	 * 
	 * @param  bool $considerFactory Considering a logger's factory
	 * @return bool
	 */
	public function hasLogger($considerFactory = true);
	
	/**
	 * Has a loggers' factory
	 * 
	 * @return bool
	 */
	public function hasLoggersFactory();
	
}