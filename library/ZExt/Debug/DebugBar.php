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

use ZExt\Html\Tag,
    ZExt\Html\ListUnordered,
    ZExt\Html\ListElement;

use ZExt\Debug\Modules\ModuleInterface,
    ZExt\Debug\Modules\Errors;

use ZExt\Dump\Html as Dump;

use Closure, Exception;

/**
 * Debug bar
 * 
 * @category   ZExt
 * @package    Debug
 * @subpackage DebugBar
 * @author     Mike.Mirten
 * @version    1.0.1
 */
class DebugBar {
	
	const NAMESPACE_MODULES = '\ZExt\Debug\Modules';
	
	/**
	 * Modules instances
	 *
	 * @var ModuleInterface[]
	 */
	protected $_modules = array();
	
	/**
	 * Deffered mode setting
	 * 
	 * @var bool
	 */
	protected $_deferredMode = false;
	
	/**
	 * Deffered mode temporary directory
	 * 
	 * @var string
	 */
	protected $_deferredTempDir;
	
	/**
	 * Unique key of the bar
	 * 
	 * @var string
	 */
	protected $_uniqueMark;
	
	/**
	 * On Shutdown callback
	 *
	 * @var Closure
	 */
	protected $_onShutdownCallback;
	
	/**
	 * Constructor
	 * 
	 * @param array $params
	 */
	public function __construct($params = null) {
		$this->_uniqueMark = md5(microtime(true) . rand(0, 1000));
		
		if (is_array($params)) {
			$this->setParameters($params);
		}
		
		register_shutdown_function(array($this, 'shutdownHandler'));
	}
	
	/**
	 * Set parameters 
	 * Calls set{"ParameterName"} methods
	 * 
	 * @param array $params
	 * @return ModuleAbstract
	 */
	public function setParameters(array $params) {
		foreach ($params as $param => $value){
			$method = 'set' . ucfirst($param);
			
			// Recursion protection
			if ($method === 'setParameters') continue;
			
			if (method_exists($this, $method)) {
				$this->$method($value);
			} else {
				$method = 'add' . ucfirst($param);
				
				if (method_exists($this, $method)) {
					$this->$method($value);
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Set the deffered mode's setting
	 * 
	 * @param bool $mode
	 */
	public function setDeferredMode($mode = true) {
		$this->_deferredMode = (bool) $mode;
	}
	
	/**
	 * Is deffered mode's setting on
	 * 
	 * @return bool
	 */
	public function isDeferredMode() {
		return $this->_deferredMode;
	}
	
	/**
	 * Set deffered temporary directory
	 * 
	 * @param string $dir
	 */
	public function setDeferredTemp($dir) {
		$this->_deferredTempDir = (string) $dir;
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
	 * Add a module
	 * 
	 * @param  ModuleInterface $module
	 * @return Debug
	 */
	public function addModule($module, $name = null, array $params = null) {
		switch (true) {
			case $module instanceof ModuleInterface:
				if ($name === null) {
					$class = get_class($module);
					$name  = substr($class, strrpos($class, '\\') + 1);
				}
				
				$this->_modules[$name] = $module;
				break;
			
			case is_string($module):
				if ($name === null) $name = $module;
				
				$this->_modules[$name] = $this->loadModule($module, $params);
				break;
			
			case is_array($module):
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
				
				$this->_modules[$name] = $this->loadModule($type, $moduleParams);
				break;
		}
		
		return $this;
	}
	
	/**
	 * Get the module
	 * 
	 * @param  string $moduleName
	 * @return ModuleInterface
	 */
	public function getModule($moduleName) {
		if (isset($this->_modules[$moduleName])) {
			return $this->_modules[$moduleName];
		}
	}
	
	/**
	 * Get modules
	 * 
	 * @return ModuleInterface[]
	 */
	public function getModules() {
		return $this->_modules;
	}
	
	/**
	 * Has the module ?
	 * 
	 * @param  string $moduleName
	 * @return bool
	 */
	public function hasModule($moduleName) {
		return isset($this->_modules[$moduleName]);
	}
	
	/**
	 * Add default modules
	 * 
	 * @return Debug
	 */
	public function addDefaultModules() {
		$this->addModules(array(
			'Versions',
			'Time',
			'Files',
			'Memory',
			'Errors'
		));
		
		return $this;
	}
	
	/**
	 * Load the module
	 * 
	 * @return ModuleInterface
	 */
	protected function loadModule($moduleName, array $params = null) {
		$class = self::NAMESPACE_MODULES . '\\' . $moduleName;
		
		return new $class($params);
	}
	
	/**
	 * Set on Shutdown callback
	 * 
	 * @param Closure $callback
	 */
	public function setOnShutdown(Closure $callback) {
		$this->_onShutdownCallback = $callback;
	}

	/**
	 * Render the debug panel or a loading script
	 * 
	 * @return string
	 */
	public function render() {
		if ($this->_deferredMode) {
			return $this->renderLoadingScript();
		} else {
			$debugBar   = $this->renderDebugBar();
			$wrapperTag = new Tag('div', $debugBar, array('id' => 'debug-wrapper'));
			
			return $wrapperTag->render();
		}
	}
	
	/**
	 * Render a loading script
	 * 
	 * @return string
	 */
	protected function renderLoadingScript() {
		$scriptPath = realpath(__DIR__ . '/View/deferred.js');
		
		$dir = $this->_deferredTempDir === null ? '' : '/' . $this->_deferredTempDir;
		$url = $dir . '/profile_' . $this->_uniqueMark . '.php';
		
		$script = file_get_contents($scriptPath);
		$script = str_replace('$url', $url, $script);

		$scriptTag = new Tag('script', $script, array('type' => 'text/javascript'));
		
		return $scriptTag->render();
	}
	
	/**
	 * Render the debug panel
	 * 
	 * @return string
	 */
	protected function renderDebugBar() {
		$modules = $this->getModules();
		if (empty($modules)) $this->addDefaultModules();
		
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
			
			if ($tab === null) continue;
			
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
			
			if (! $panel) continue;
			
			$id = 'debug-panel-' . substr(md5($name), 16);
			
			$tabElement->addClass('withpanel clickable');
			$tabElement->setAttr('data-panel-id', $id);
			
			$titleTag = new Tag('h4', $name, 'debug-bar-wrapper');
			$panelTag = new Tag('div', $panel, 'debug-panel');
			
			$panels .= new Tag('div', $titleTag . $panelTag, array(
				'id'    => $id,
				'class' => 'debug-panel-wrapper'
			));
			
			unset($exception);
		}
		
		$stylePath = realpath(__DIR__ . '/View/bar.css');
		$styleTag = new Tag('style', file_get_contents($stylePath));
		
		$scriptPath = realpath(__DIR__ . '/View/bar.js');
		$scriptTag = new Tag('script', file_get_contents($scriptPath), array('type' => 'text/javascript'));
		
		$wrapperTag = new Tag('div', $tabsList, 'debug-bar-wrapper');
		$debugTag   = new Tag('div', $panels . $wrapperTag, 'debug-main');
		
		return $styleTag . $scriptTag . $debugTag;
	}
	
	public function __toString() {
		return $this->render();
	}
	
	/**
	 * Shutdown handler
	 */
	public function shutdownHandler() {
		if ($this->_onShutdownCallback !== null) {
			$this->_onShutdownCallback->__invoke($this);
		}
		
		$error = error_get_last();
		
		if (! empty($error) && $error['type'] === E_ERROR) {
			foreach ($this->getModules() as $module) {
				if ($module instanceof Errors) break;
			}
			
			if (! isset($module)) {
				$this->addModule('Error');
			}
			
			$module->errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
			
			echo $this->renderDebugBar();
		}
		else if ($this->_deferredMode) {
			if ($this->_deferredTempDir !== null) {
				$dir = '/' . $this->_deferredTempDir;

				$realDir = $_SERVER['DOCUMENT_ROOT'] . $dir;
				if (! is_dir($realDir)) {
					mkdir($realDir, 0755);
				}
			} else {
				$dir = '';
			}

			$filePath = $_SERVER['DOCUMENT_ROOT'] . $dir . '/profile_' . $this->_uniqueMark . '.php';

			$scriptPath = realpath(__DIR__ . '/View/bar.php');
			$scriptPhp  = file_get_contents($scriptPath);
			
			file_put_contents($filePath, $scriptPhp . PHP_EOL . $this->renderDebugBar());
		}
	}
	
}