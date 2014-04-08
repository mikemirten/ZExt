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

use ZExt\Log\Exceptions\NoDirectory;
use ZExt\Log\Exceptions\ConfigError;
use ZExt\Log\Adapters\Dummy;

use Closure;

/**
 * Loggers' factory
 * 
 * @category   ZExt
 * @package    Logger
 * @subpackage Factory
 * @author     Mike.Mirten
 * @version    1.0RC1
 */
class Factory implements FactoryInterface {
	
	const PATTERN_SERVICE = '%service%';
	const PATTERN_DATE    = '%date%';
	
	/**
	 * Base logs path
	 *
	 * @var string
	 */
	protected $_basePath;
	
	/**
	 * Separate logs dir for each service
	 *
	 * @var bool
	 */
	protected $_separateDirs = false;
	
	/**
	 * Filenames' format template
	 *
	 * @var string
	 */
	protected $_filenameTemplate = '%service%.log';
	
	/**
	 * Date format template
	 *
	 * @var string
	 */
	protected $_dateFormat = 'Y.m.d';
	
	/**
	 * Create callback
	 *
	 * @var Closure
	 */
	protected $_createCallback;
	
	/**
	 * Created loggers
	 *
	 * @var LoggerInterface[]
	 */
	protected $_loggers = [];
	
	/**
	 * Constructor
	 * 
	 * @param string  $basePath
	 * @param Closure $createCallback
	 */
	public function __construct($basePath = null, Closure $createCallback = null) {
		if ($basePath !== null) {
			$this->setBasePath($basePath);
		}
		
		if ($createCallback !== null) {
			$this->setCreateCallback($createCallback);
		}
	}
	
	/**
	 * Create a logger for the service
	 * 
	 * @param  string $serviceName
	 * @return LoggerInterface
	 * @throws ConfigError
	 * @throws NoDirectory
	 */
	public function createForService($serviceName) {
		if ($this->_basePath === null) {
			throw new ConfigError('Base path was not given');
		}
		
		$filename = $this->_filenameTemplate;
		
		if (strpos($filename, self::PATTERN_SERVICE) !== false) {
			$filename = str_replace(self::PATTERN_SERVICE, $serviceName, $filename);
		}
		
		if (strpos($filename, self::PATTERN_DATE) !== false) {
			$filename = str_replace(self::PATTERN_DATE, date($this->_dateFormat), $filename);
		}
		
		$path = $this->_basePath;
		
		if ($this->_separateDirs) {
			$path .= DIRECTORY_SEPARATOR . $serviceName;
			
			if ((! file_exists($path) || ! is_dir($path)) && ! mkdir($path)) {
				throw new NoDirectory('Directory does not exists and unable to create: "' . $path . '"');
			}
		}
		
		$path .= DIRECTORY_SEPARATOR . $filename;
		
		if (isset($this->_loggers[$serviceName])) {
			return $this->_loggers[$serviceName];
		}
		
		$adapter = $this->getCreateCallback()->__invoke($path);
		$logger  = new Logger($adapter);
		
		$this->_loggers[$serviceName] = $logger;
		return $logger;
	}
	
	/**
	 * Set callback, which will create an adapter
	 * 
	 * @param  Closure $callback
	 * @return Factory
	 */
	public function setCreateCallback(Closure $callback) {
		$this->_createCallback = $callback;
		
		return $this;
	}
	
	/**
	 * Get callback, which will create an adapter
	 * 
	 * @return Closure
	 */
	public function getCreateCallback() {
		if ($this->_createCallback === null) {
			$this->_createCallback = function($path) {
				return new Dummy();
			};
		}
		
		return $this->_createCallback;
	}
	
	/**
	 * Set the base path
	 * 
	 * @param  string $path
	 * @return Factory
	 * @throws ConfigError
	 */
	public function setBasePath($path) {
		$path = rtrim($path, DIRECTORY_SEPARATOR);
		
		if (! file_exists($path) || ! is_dir($path)) {
			throw new ConfigError('Base directory does not exists: "' . $path . '"');
		}
		
		$this->_basePath = $path;
		
		return $this;
	}
	
	/**
	 * Get the base path
	 * 
	 * @return string
	 */
	public function getBasePath() {
		return $this->_basePath;
	}
	
	/**
	 * Set using of the separate log's dir for each service
	 * 
	 * @param bool $flag
	 */
	public function setSeparateDirs($flag = true) {
		$this->_separateDirs = (bool) $flag;
	}
	
	/**
	 * Is separate log's dir for each service enabled ?
	 * 
	 * @return bool
	 */
	public function isSeparateDirs() {
		return $this->_separateDirs;
	}
	
	/**
	 * Set the filename template
	 * 
	 * @param  string $template
	 * @return Factory
	 */
	public function setFilenameTemplate($template) {
		$this->_filenameTemplate = (string) $template;
		
		return $this;
	}
	
	/**
	 * Get the filename template
	 * 
	 * @return string
	 */
	public function getFilenameTemplate() {
		return $this->_filenameTemplate;
	}
	
	/**
	 * Set the date format for the filename
	 * 
	 * @param  string $format
	 * @return Factory
	 */
	public function setDateFormat($format) {
		$this->_dateFormat = (string) $format;
		
		return $this;
	}
	
	/**
	 * Set the date format for the filename
	 * 
	 * @return string
	 */
	public function getDateFormat() {
		return $this->_dateFormat;
	}
	
}