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

namespace ZExt\Loader;

use ZExt\Loader\Exceptions\InvalidPath;
use ZExt\Loader\Exceptions\InvalidNamespace;
use ZExt\Loader\Exceptions\RegistrationFailed;

use stdClass, SplStack;

/**
 * Autoloader
 * 
 * @category   ZExt
 * @package    Loader
 * @subpackage Autoloader
 * @author     Mike.Mirten
 * @version    1.0beta
 */
class Autoloader {
	
	/**
	 * Is statically registered ?
	 *
	 * @var bool
	 */
	static protected $registeredStatic = false;
	
	/**
	 * Is already registered ?
	 *
	 * @var bool
	 */
	protected $registered = false;
	
	/**
	 * Namespaces
	 *
	 * @var SplStack
	 */
	protected $namespaces;
	
	/**
	 * Directories
	 *
	 * @var SplStack
	 */
	protected $dirs;
	
	/**
	 * Register autloader with default namespaces
	 * 
	 * @return Autoloader
	 */
	static public function registerDefaults() {
		if (self::$registeredStatic) {
			require_once __DIR__ . '/Exceptions/RegisterationFailed.php';
			throw new RegistrationFailed('Is already registered');
		}
		
		$loader = new static();
		$loader->registerNamespace('ZExt', __DIR__ . '/..');
		$loader->register();
		
		self::$registeredStatic = true;
		
		return $loader;
	}
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->namespaces = new SplStack();
		$this->dirs       = new SplStack();
	}
	
	/**
	 * Register the namespace
	 * 
	 * @param  array $namespaces [namespace => dir(s)]
	 * @return Autoloader
	 */
	public function registerNamespaces(array $namespaces, $prepend = false) {
		foreach ($namespaces as $namespace => $path) {
			$this->registerNamespace($namespace, $path, $prepend);
		}
		
		return $this;
	}
	
	/**
	 * Register the namespace
	 * 
	 * @param  string         $namespace
	 * @param  string | array $path
	 * @param  bool           $prepend
	 * @return Autoloader
	 * @throws InvalidPath
	 */
	public function registerNamespace($namespace, $path, $prepend = false) {
		if (! isset($namespace[0])) {
			require_once __DIR__ . '/Exceptions/InvalidNamespace.php';
			throw new InvalidNamespace('Namespace cannot be empty');
		}
		
		$definition = new stdClass();
		$definition->namespace = trim($namespace, '\ ');
		$definition->dirs      = new SplStack();
		
		if (is_array($path)) {
			foreach ($path as $dir) {
				$definition->dirs[] = $this->normalizePath($dir);
			}
		} else {
			$definition->dirs[] = $this->normalizePath($path);
		}
		
		if ($prepend) {
			$this->namespaces->shift($definition);
		} else {
			$this->namespaces[] = $definition;
		}
		
		return $this;
	}
	
	/**
	 * Register the directories
	 * 
	 * @param  array $dirs
	 * @param  bool  $prepend
	 * @return Autoloader
	 */
	public function registerDirs(array $dirs, $prepend = false) {
		foreach ($dirs as $path) {
			$this->registerDir($path, $prepend);
		}
		
		return $this;
	}
	
	/**
	 * Register the directory
	 * 
	 * @param  string $path
	 * @param  bool   $prepend
	 * @return Autoloader
	 */
	public function registerDir($path, $prepend = false) {
		$path = $this->normalizePath($path);
		
		if ($prepend) {
			$this->dirs->shift($path);
		} else {
			$this->dirs[] = $path;
		}
		
		return $this;
	}
	
	/**
	 * Normalize the path
	 * 
	 * @param  string $path
	 * @return string
	 */
	protected function normalizePath($path) {
		$path = realpath(rtrim($path, DIRECTORY_SEPARATOR));
		
		if ($path === false || ! is_readable($path)) {
			require_once __DIR__ . '/Exceptions/InvalidPath.php';
			throw new InvalidPath('Directory "' . $path . '" does not exists or is unreadable');
		}
		
		return $path;
	}
	
	/**
	 * Register the loader
	 */
	public function register() {
		if ($this->registered) {
			require_once __DIR__ . '/Exceptions/RegisterationFailed.php';
			throw new RegistrationFailed('Is already registered');
		}
		
		if ($this->namespaces->isEmpty()) {
			require_once __DIR__ . '/Exceptions/RegisterationFailed.php';
			throw new RegistrationFailed('No namespaces was registered');
		}
		
		spl_autoload_register(function($class) {
			foreach ($this->namespaces as $definition) {
				if (strpos($class, $definition->namespace) === 0) {
					$resolvedClass = substr($class, strlen($definition->namespace));
					
					if ($this->loadClass($resolvedClass, $definition->dirs)) {
						return;
					}
				}
			}
			
			if (! $this->dirs->isEmpty()) {
				$this->loadClass('\\' . $class, $this->dirs);
			}
		});
		
		$this->registered = true;
	}
	
	/**
	 * Load the class
	 * 
	 * @param  string   $class
	 * @param  SplStack $path
	 * @return bool
	 */
	protected function loadClass($class, SplStack $dirs) {
		$script = str_replace(['_', '\\'], DIRECTORY_SEPARATOR, $class) . '.php';
		
		foreach ($dirs as $dir) {
			$path = $dir . $script;
			
			if (is_file($path)) {
				include $path;
				return true;
			}
		}
		
		return false;
	}
	
}