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

namespace ZExt\Log\Adapters;

use Phalcon\Logger\AdapterInterface as PhalconAdapterInterface;

/**
 * Logger based on Phalcon\Logger\Adapter\File;
 * 
 * @category   ZExt
 * @package    Logger
 * @subpackage Logger
 * @author     Mike.Mirten
 * @version    1.0
 */
class Phalcon implements AdapterInterface {
	
	/**
	 * Phalcon file adapter
	 *
	 * @var PhalconAdapterInterface
	 */
	protected $adapter;
	
	/**
	 * Constructor
	 * 
	 * @param PhalconAdapterInterface $adapter
	 */
	public function __construct(PhalconAdapterInterface $adapter) {
		$this->adapter = $adapter;
	}

	/**
	 * Log the message
	 * 
	 * @param string $message
	 * @param int    $code
	 */
	public function log($message, $code = self::INFO) {
		$this->adapter->log($code, $message);
	}

}