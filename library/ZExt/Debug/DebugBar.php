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

namespace ZExt\Debug;

use ZExt\Components\OptionsTrait;

use ZExt\Profiler\ProfilerInterface,
    ZExt\Profiler\ProfilerExtendedInterface,
    ZExt\Profiler\ProfileableInterface;

use ZExt\Html\Tag,
    ZExt\Html\ListUnordered,
    ZExt\Html\ListElement;

use ZExt\Debug\Modules\ModuleInterface,
    ZExt\Debug\Modules\Errors;

use ZExt\Dump\Html as Dump;

use Closure, Exception;

use ZExt\Debug\Exceptions\InvalidPath;

/**
 * Debug bar
 * 
 * @category   ZExt
 * @package    Debug
 * @subpackage DebugBar
 * @author     Mike.Mirten
 * @version    1.1
 */
class DebugBar {
	
	use OptionsTrait;
	
	const NAMESPACE_MODULES = '\ZExt\Debug\Modules';
	
	/**
	 * Modules instances
	 *
	 * @var ModuleInterface[]
	 */
	protected $modules = [];
	
	/**
	 * Deferred mode setting
	 * 
	 * @var bool
	 */
	protected $deferredMode = false;
	
	/**
	 * Deffered mode temporary directory
	 * 
	 * @var string
	 */
	protected $deferredDir;
	
	/**
	 * Deffered mode url
	 * 
	 * @var string
	 */
	protected $deferredUrl = '/';
	
	/**
	 * Unique key of the bar
	 * 
	 * @var string
	 */
	protected $token;
	
	/**
	 * On Shutdown callback
	 *
	 * @var Closure
	 */
	protected $onShutdownCallback;
	
	/**
	 * Constructor
	 * 
	 * @param array | \Traversable $options
	 */
	public function __construct($options = null) {
		$this->token       = md5(microtime(true) . rand(0, 1000));
		$this->deferredDir = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR);
		
		if ($options !== null) {
			$this->setOptions($options);
		}
		
		$this->registerShutdownHandler();
	}
	
	/**
	 * Set the deffered mode's setting
	 * 
	 * @param  bool $mode
	 * @return DebugBar
	 */
	public function setDeferredMode($mode = true) {
		$this->deferredMode = (bool) $mode;
		
		return $this;
	}
	
	/**
	 * Is deffered mode's setting on
	 * 
	 * @return bool
	 */
	public function isDeferredMode() {
		return $this->deferredMode;
	}
	
	/**
	 * Set deffered temporary directory
	 * 
	 * @param  string $dir Directory with profiles
	 * @param  string $url Url path to profiles
	 * @return DebugBar
	 * @throws InvalidPath
	 */
	public function setDeferredPath($dir, $url = null) {
		$dir = realpath($dir);
		
		if ($dir === false || ! is_writable($dir)) {
			throw new InvalidPath('The directory "' . $dir . '" must exists and be a writable');
		}
		
		$this->deferredDir = $dir;
		
		if ($url === null) {
			$this->deferredUrl = str_replace(rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR), '', $dir) . '/';
		} else {
			$this->deferredUrl = rtrim($url, '/') . '/';
		}
		
		return $this;
	}
	
	/**
	 * Add modules
	 * 
	 * @param  array $modules
	 * @return Debug
	 */
	public function addModules(array $modules) {
		foreach ($modules as $name => $module) {
			if (is_string($name)) {
				$this->addModule($module, $name);
			} else {
				$this->addModule($module);
			}
		}
		
		return $this;
	}
	
	/**
	 * Add the profiler
	 * 
	 * @param  ProfilerInterface | ProfileableInterface $profiler
	 * @param  string $name
	 * @return Debug
	 */
	public function addProfiler($profiler, $name = null) {
		if ($profiler instanceof ProfileableInterface) {
			if ($profiler->isProfilerEnabled()) {
				$profiler = $profiler->getProfiler();
			}
		}
		
		if ($profiler instanceof ProfilerInterface) {
			if ($name === null && $profiler instanceof ProfilerExtendedInterface) {
				$name = $profiler->getName();
			}
			
			$this->addModule('profiler', $name, [
				'profiler' => $profiler
			]);
		}
		
		return $this;
	}
	
	/**
	 * Add a module
	 * 
	 * @param  ModuleInterface | string | array $module
	 * @param  string                           $name
	 * @param  array                            $params
	 * 
	 * @return DebugBar
	 */
	public function addModule($module, $name = null, array $params = null) {
		// Module instance
		if ($module instanceof ModuleInterface) {
			if ($name === null) {
				$class = get_class($module);
				$name  = substr($class, strrpos($class, '\\') + 1);
			}

			$this->modules[$name] = $module;
		}
		// String definition
		else if (is_string($module)) {
			if ($name === null) {
				$name = $module;
			}

			$this->modules[$name] = $this->loadModule($module, $params);
		}
		// Array definition
		else if (is_array($module)) {
			$type = isset($module['type']) ? $module['type'] : $module[0];

			// Define a name
			switch (true) {
				case $name !== null:
					break;

				case isset($module['name']):
					$name = $module['name'];
					break;

				case count($module) > 2:
					$name = $module[1];
					break;

				default:
					$name = $type;
			}

			// Define a params
			switch (true) {
				case isset($module['params']):
					$moduleParams = $module['params'];
					break;

				case count($module) > 2:
					$moduleParams = $module[2];
					break;

				default:
					$moduleParams = $module[1];
			}

			if ($params !== null) {
				$moduleParams += $params;
			}

			$this->modules[$name] = $this->loadModule($type, $moduleParams);
		}
		
		return $this;
	}
	
	/**
	 * Get the module
	 * 
	 * @param  string $moduleName
	 * @return ModuleInterface | null
	 */
	public function getModule($moduleName) {
		if (isset($this->modules[$moduleName])) {
			return $this->modules[$moduleName];
		}
	}
	
	/**
	 * Get all modules
	 * 
	 * @return ModuleInterface[]
	 */
	public function getModules() {
		return $this->modules;
	}
	
	/**
	 * Has the module ?
	 * 
	 * @param  string $moduleName
	 * @return bool
	 */
	public function hasModule($moduleName) {
		return isset($this->modules[$moduleName]);
	}
	
	/**
	 * Add default modules
	 * 
	 * @return Debug
	 */
	public function addDefaultModules() {
		$this->addModules([
			'versions',
			'time',
			'files',
			'memory',
			'errors'
		]);
		
		return $this;
	}
	
	/**
	 * Load the module
	 * 
	 * @return ModuleInterface
	 */
	protected function loadModule($moduleName, array $params = null) {
		$class = self::NAMESPACE_MODULES . '\\' . ucfirst($moduleName);
		
		return new $class($params);
	}
	
	/**
	 * Set on Shutdown callback
	 * 
	 * @param Closure $callback
	 */
	public function setOnShutdown(Closure $callback) {
		$this->onShutdownCallback = $callback;
	}

	/**
	 * Render the debug panel or a loading script
	 * 
	 * @return string
	 */
	public function render() {
		if ($this->deferredMode) {
			return $this->renderLoadingScript();
		}
		
		$debugBar   = $this->renderDebugBar();
		$wrapperTag = new Tag('div', $debugBar, ['id' => 'debug-wrapper']);
		
		return $wrapperTag->render();
	}
	
	/**
	 * Render a loading script
	 * 
	 * @return string
	 */
	protected function renderLoadingScript() {
		$url = $this->deferredUrl . 'profile_' . $this->token . '.html';
		
		$script = $this->getAsset('deferred.js');
		$script = str_replace('$url', $url, $script);

		$scriptTag = new Tag('script', $script, ['type' => 'text/javascript']);
		
		return $scriptTag->render();
	}
	
	/**
	 * Render the debug panel
	 * 
	 * @return string
	 */
	protected function renderDebugBar() {
		$modules = $this->getModules();
		
		if (empty($modules)) {
			$this->addDefaultModules();
		}
		
		$tabsList = new ListUnordered();
		$tabsList->setSeparator('');
		$tabsList->addClass('debug-bar');
		$tabsList->setAttr('id', 'debug-elements');
		
		$panels = '';
		
		foreach ($this->getModules() as $name => $module) {
			try {
				$tab = $module->renderTab();
			} catch(Exception $exception) {
				$tab = 'Error';
			}
			
			if ($tab === null) {
				continue;
			}
			
			if (isset($exception)) {
				$panel = 'Tab exception: ';
			} else {
				try {
					$panel = $module->renderPanel();
				} catch(Exception $exception) {
					$tab   = 'Error';
					$panel = 'Panel exception: ';
				}
			}
			
			$infoTag = new Tag('span', $tab, 'debug-info-holder');
			
			if (isset($exception)) {
				$panel .= Dump::getDump($exception);
				
				$infoTag->addStyle('color', 'red');
			}
			
			$tabElement = new ListElement($infoTag);
			$tabElement->addClass('debug-tab');
			$tabElement->setAttr('title', $name . ': ' . $tab);
			
			$icon = $module->getTabIcon();
			if ($icon) {
				$tabElement->addClass('hasicon');
				$tabElement->addStyle('background-image', "url($icon)");
			}
			
			$tabsList->addElement($tabElement);
			
			if (! $panel) {
				continue;
			}
			
			$id = 'debug-panel-' . substr(md5($name), 16);
			
			$tabElement->addClass('withpanel clickable');
			$tabElement->setAttr('data-panel-id', $id);
			
			$titleTag = new Tag('h4', $name, 'debug-bar-wrapper');
			$panelTag = new Tag('div', $panel, 'debug-panel');
			
			$panels .= new Tag('div', $titleTag . $panelTag, [
				'id'    => $id,
				'class' => 'debug-panel-wrapper'
			]);
			
			unset($exception);
		}
		
		$styleTag  = new Tag('style', $this->getAsset('bar.css'));
		$scriptTag = new Tag('script', $this->getAsset('bar.js'), ['type' => 'text/javascript']);
		
		$wrapperTag = new Tag('div', $tabsList, 'debug-bar-wrapper');
		$debugTag   = new Tag('div', $panels . $wrapperTag, 'debug-main');
		
		return $styleTag . $scriptTag . $debugTag;
	}
	
	/**
	 * Get the path to the asset
	 * 
	 * @param  string $name
	 * @return string
	 * @throws InvalidPath
	 */
	protected function getAsset($name) {
		$path  = __DIR__ . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . $name;
		$asset =  file_get_contents($path);
		
		if ($asset === false) {
			throw new InvalidPath('Unable to get asset "' . $name . '" by path "' . $path . '"');
		}
		
		return $asset;
	}
	
	/**
	 * Render the debug bar
	 * 
	 * @return string
	 */
	public function __toString() {
		try {
			return $this->render();
		} catch (Exception $exception) {
			return Dump::dump($exception);
		}
	}
	
	/**
	 * Shutdown handler
	 */
	protected function registerShutdownHandler() {
		register_shutdown_function(function() {
			// Emergency autoloader
			$incPath = realpath(__DIR__ . '/../..') . DIRECTORY_SEPARATOR;
			
			spl_autoload_register(function($class) use($incPath) {
				if (strpos($class, 'ZExt') === 0) {
					include($incPath . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');
				}
			});
			
			if ($this->onShutdownCallback !== null) {
				$this->onShutdownCallback->__invoke($this);
			}

			// Last error handling
			$error = error_get_last();
			
			if (! empty($error) && $error['type'] === E_ERROR) {
				foreach ($this->getModules() as $module) {
					if ($module instanceof Errors) {
						break;
					}
					
					unset($module);
				}
				
				if (! isset($module)) {
					$module = new Errors();
					$this->addModule($module);
				}
				
				$module->errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
				
				echo $this->renderDebugBar();
				return;
			}
			
			// Debug bar rendering for the deferred mode
			if ($this->deferredMode) {
				$filePath = $this->deferredDir . DIRECTORY_SEPARATOR . 'profile_' . $this->token . '.html';
				
				file_put_contents($filePath, $this->renderDebugBar());
			}
		});
	}
	
}