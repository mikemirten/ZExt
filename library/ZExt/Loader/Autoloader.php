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
	}
	
	/**
	 * Register the namespace
	 * 
	 * @param  array $namespaces [namespace => dir(s)]
	 * @return Autoloader
	 */
	public function registerNamespaces(array $namespaces) {
		foreach ($namespaces as $namespace => $path) {
			$this->registerNamespace($namespace, $path);
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
			$class = trim($class, '\ ');
			
			foreach ($this->namespaces as $definition) {
				if (strpos($class, $definition->namespace) === 0) {
					$class = str_replace($definition->namespace, '', $class);
					$this->loadClass($class, $definition->dirs);
					return;
				}
			}
		});
		
		$this->registered = true;
	}
	
	/**
	 * Load the class
	 * 
	 * @param string $class
	 * @param string $path
	 */
	protected function loadClass($class, $dirs) {
		$script = str_replace(['_', '\\'], DIRECTORY_SEPARATOR, $class) . '.php';
		
		foreach ($dirs as $dir) {
			$path = $dir . $script;
			
			if (is_file($path)) {
				include $path;
				return;
			}
		}
	}
	
}