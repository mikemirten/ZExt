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
namespace ZExt\Db\Phalcon\Adapter;

use Phalcon\Db\Adapter\Pdo\Mysql as PhalconMysql;
use ZExt\Profiler\ProfileableInterface;

/**
 * Phalcon Mysql adapter
 * 
 * @package    Db
 * @subpackage Phalcon
 * @author     Mike.Mirten
 * @version    1.0
 */
class Mysql extends PhalconMysql implements ProfileableInterface {
	
	use AdapterTrait;
	
	const PARAM_PROFILER = 'profiler';
	
	/**
	 * Constructor
	 * 
	 * @param array $options
	 */
	public function __construct($options) {
		$this->handleOptions($options);
		parent::__construct($options);
	}
	
	/**
	 * Profiler initialization
	 */
	protected function onProfilerInit($profiler) {
		$profiler->setName('Phalcon MySQL')
		         ->setIcon('db');
	}
	
}