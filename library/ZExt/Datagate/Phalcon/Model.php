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

namespace ZExt\Datagate\Phalcon;

use Phalcon\Mvc\Model as ModelAbstract;

use ZExt\Datagate\DatagateInterface;
use ZExt\Datagate\Phalcon\Exceptions\NoDatagate;

/**
 * Phalcon model extension
 * 
 * @category   ZExt
 * @package    Datagate
 * @subpackage Phalcon
 * @author     Mike.Mirten
 * @version    1.0dev
 */
class Model extends ModelAbstract {
	
	/**
	 * Parent datagate
	 *
	 * @var DatagateInterface 
	 */
	private $_datagate;
	
	/**
	 * Set the parent datagate
	 * 
	 * @param DatagateInterface $datagate
	 */
	public function setDatagate(DatagateInterface $datagate) {
		$this->setSource($datagate->getTableName());
		$this->_datagate = $datagate;
	}
	
	/**
	 * Return the parent datagate
	 * 
	 * @return DatagateInterface
	 */
	public function getDatagate() {
		if ($this->_datagate === null) {
			throw new NoDatagate('Datagate must be supplied');
		}
		
		return $this->_datagate;
	}
	
}