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

namespace ZExt\Model;

use Countable;

/**
 * Models' interface
 * 
 * @category   ZExt
 * @package    Model
 * @subpackage ModelInterface
 * @author     Mike.Mirten
 * @version    2.0
 */
interface ModelInterface extends Countable {
	
	/**
	 * Models' factory
	 * 
	 * @param array $data
	 * @return ModelInterface
	 */
	public static function factory(array &$data = null);
	
	/**
	 * Constructor
	 * 
	 * @param array $data
	 */
	public function __construct(array &$data = null);

	/**
	 * Set model's data
	 * 
	 * @param array $data
	 */
	public function setData(array $data);
	
	/**
	 * Set linked model's data
	 * 
	 * @param array $data
	 */
	public function setDataLinked(array &$data);
	
	/**
	 * Get model's data
	 * 
	 * @return array
	 */
	public function getData();
	
	/**
	 * Get linked model's data
	 * 
	 * @return array
	 */
	public function &getDataLinked();
	
	/**
	 * Model is empty check
	 * 
	 * @return bool
	 */
	public function isEmpty();
	
	/**
	 * Set the name of a parent service
	 * 
	 * @param string | bool $seviceName 
	 */
	public function setParentService($seviceName);
	
	/**
	 * Get the name of a parent service
	 * 
	 * @return string
	 */
	public function getParentService();
	
	/**
	 * Set a name of a parental datagate
	 *  
	 * @param string $datagateName
	 */
	public function setParentDatagate($datagateName);
	
	/**
	 * Get a name of a parental datagate
	 * 
	 * @return string
	 */
	public function getParentDatagate();
	
	/**
	 * Get model's metadata
	 * 
	 * @return Object
	 */
	public function getMetadata();
	
	/**
	 * Model has metadata
	 * 
	 * @return bool
	 */
	public function hasMetadata();
	
	/**
	 * Save a model or changes of a model
	 */
	public function save();
	
	/**
	 * Remove model
	 */
	public function remove();
	
	/**
	 * Get a name of a model
	 * 
	 * @return string
	 */
	public function getName();
	
	/**
	 * Has been the "insert" method forsed for a model
	 * 
	 * @return bool
	 */
	public function isInsertForced();
	
}