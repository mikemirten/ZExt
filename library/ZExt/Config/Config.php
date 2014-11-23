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

use ZExt\Config\Exceptions\ReadOnly;
use ArrayIterator;

/**
 * Configuration holder
 * 
 * @category   ZExt
 * @package    Config
 * @subpackage Config
 * @author     Mike.Mirten
 * @version    2.0
 */
class Config implements ConfigInterface {
	
	/**
	 * Source data
	 *
	 * @var array
	 */
	protected $_data = [];
	
	/**
	 * Read only mode lock
	 *
	 * @var bool
	 */
	protected $_locked = false;
	
	/**
	 * Constructor
	 * 
	 * @param array $source
	 * @param bool  $lock
	 */
	public function __construct(array $source = null, $lock = true) {
		if ($source !== null) {
			$this->setSourceData($source);
			
			if ($lock) {
				$this->lock();
			}
		}
	}
	
	/**
	 * Get parameter
	 * 
	 * @param  string $name      Parameter's name
	 * @param  string $delimiter Nesting delimiter
	 * @return mixed
	 */
	public function get($name, $delimiter = self::DELIMITER) {
		if (strpos($name, $delimiter) === false) {
			return isset($this->_data[$name])
				? $this->_data[$name]
				: null;
		}
		
		list ($first, $rest) = explode($delimiter, $name, 2);
		
		if (isset($this->_data[$first]) && $this->_data[$first] instanceof self) {
			return $this->_data[$first]->get($rest, $delimiter);
		}
	}
	
	/**
	 * Set parameter
	 * 
	 * @param  string $name      Parameter's name
	 * @param  mixed  $value     Parameters value
	 * @param  string $delimiter Nesting delimiter
	 * @return Config
	 * @throws ReadOnly
	 */
	public function set($name, $value, $delimiter = self::DELIMITER) {
		if ($this->_locked) {
			throw new ReadOnly('Config is read only');
		}
		
		if (strpos($name, $delimiter) === false) {
			$this->_data[$name] = is_array($value)
				? new static($value, false)
				: $value;
			
			return $this;
		}
		
		list ($first, $rest) = explode($delimiter, $name, 2);
		
		if (! isset($this->_data[$first]) || ! $this->_data[$first] instanceof self) {
			$this->_data[$first] = new static();
		}
		
		$this->_data[$first]->set($rest, $value, $delimiter);
		
		return $this;
	}
	
	/**
	 * Has parameter ?
	 * 
	 * @param type $name      Parameter's name
	 * @param type $delimiter Nesting delimiter
	 */
	public function has($name, $delimiter = self::DELIMITER) {
		if (strpos($name, $delimiter) === false) {
			return isset($this->_data[$name]);
		}
		
		list ($first, $rest) = explode($delimiter, $name, 2);
		
		if (isset($this->_data[$first]) && $this->_data[$first] instanceof self) {
			return $this->_data[$first]->has($rest, $delimiter);
		}
		
		return false;
	}
	
	/**
	 * Remove parameter
	 * 
	 * @param  string $name      Parameter's name
	 * @param  string $delimiter Nesting delimiter
	 * @return Config
	 * @throws ReadOnly
	 */
	public function remove($name, $delimiter = self::DELIMITER) {
		if ($this->_locked) {
			throw new ReadOnly('Config is read only');
		}
		
		if (strpos($name, $delimiter) === false) {
			unset($this->_data[$name]);
			
			return $this;
		}
		
		list ($first, $rest) = explode($delimiter, $name, 2);
		
		if (isset($this->_data[$first]) && $this->_data[$first] instanceof self) {
			$this->_data[$first]->remove($rest, $delimiter);
		}
		
		return $this;
	}
	
	/**
	 * Parse a source data from a json string
	 * 
	 * @param  string $json
	 * @return Config
	 */
	public function fromJson($json) {
		$this->setSourceData(json_decode($json));
		
		return $this;
	}

	/**
	 * Set a source data for the config
	 * 
	 * @param  array $source
	 * @return Config
	 * @throws ReadOnly
	 */
	public function setSourceData(array $source) {
		if ($this->_locked) {
			throw new ReadOnly('Config is read only');
		}
		
		foreach ($source as $key => $part) {
			if (is_array($part)) {
				$this->_data[$key] = new static($part, false);
			} else {
				$this->_data[$key] = $part;
			}
		}
		
		return $this;
	}
	
	/**
	 * Lock the config to the read only mode
	 * 
	 * @return Config
	 */
	public function lock() {
		$this->_locked = true;
		
		foreach ($this->_data as $part) {
			if ($part instanceof Config) {
				$part->lock();
			}
		}
		
		return $this;
	}
	
	/**
	 * Is the config in the read only mode
	 * 
	 * @return bool
	 */
	public function isLocked() {
		return $this->_locked;
	}
	
	/**
	 * Merge a config into this config
	 * 
	 * @param  ConfigInterface $config
	 * @return Config
	 * @throws ReadOnly
	 */
	public function merge(ConfigInterface $config) {
		if ($this->_locked) {
			throw new ReadOnly('Config is read only');
		}
		
		$source = array_replace_recursive($this->toArray(), $config->toArray());
		$this->setSourceData($source);
		
		return $this;
	}
	
	/**
	 * Get the config as an array
	 * 
	 * @return array
	 */
	public function toArray() {
		$result = [];
		
		foreach ($this->_data as $key => $part) {
			if ($part instanceof Config) {
				$result[$key] = $part->toArray();
			} else {
				$result[$key] = $part;
			}
		}
		
		return $result;
	}
	
	/**
	 * Get the config as a flat (non recursive) array
	 * 
	 * @param  string $delimiter
	 * @return array
	 */
	public function toFlatArray($delimiter = self::DELIMITER) {
		$result = [];
		
		foreach ($this->_data as $key => $part) {
			if ($part instanceof Config) {
				foreach ($part->toFlatArray($delimiter) as $subkey => $subpart) {
					$result[$key . $delimiter . $subkey] = $subpart;
				}
			} else {
				$result[$key] = $part;
			}
		}
		
		return $result;
	}
	
	/**
	 * Get the config as json string
	 * 
	 * @return string
	 */
	public function toJson() {
		return json_encode($this->toArray());
	}
	
	/**
	 * Get iterator for the foreach traversing
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return new ArrayIterator($this->_data);
	}
	
	/**
	 * Get the number of the config elements
	 * 
	 * @return int
	 */
	public function count() {
		return count($this->_data);
	}

	/**
	 * Set a config's property
	 * 
	 * @param  string | int $name
	 * @param  mixed        $value
	 * @throws ReadOnly
	 */
	public function __set($name, $value) {
		$this->set($name, $value);
	}
	
	/**
	 * Get a config's property
	 * 
	 * @param  string | int $name
	 * @return mixed
	 */
	public function __get($name) {
		return $this->get($name);
	}
	
	/**
	 * Has a config's property
	 * 
	 * @param  string | int $name
	 * @return bool
	 */
	public function __isset($name) {
		return $this->has($name);
	}
	
	/**
	 * Remove a config's property
	 * 
	 * @param  string | int $name
	 * @throws ReadOnly
	 */
	public function __unset($name) {
		return $this->remove($name);
	}
	
	/**
	 * Recursive cloning of the config
	 */
	public function __clone() {
		$this->_locked = false;
		
		foreach ($this->_data as $key => $part) {
			if ($part instanceof Config) {
				$this->_data[$key] = clone $part;
			}
		}
	}
	
}