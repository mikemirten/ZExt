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

use ZExt\Di\LocatorByArgumentsInterface,
    ZExt\Di\InitializerNamespace;

use ZExt\Profiler\ProfilerInterface,
    ZExt\Profiler\ProfilerExtendedInterface,
    ZExt\Profiler\ProfileableInterface;

use ZExt\Html\Script;

use ZExt\Debug\Infosets\Infoset,
    ZExt\Debug\Collectors\CollectorInterface,
    ZExt\Debug\Renderers\RendererInterface,
    ZExt\Debug\Renderers\Html as RendererHtml;

use ZExt\Dump\Html as Dump;

use Closure, Exception, DirectoryIterator;

use ZExt\Debug\Exceptions\InvalidPath,
    ZExt\Debug\Exceptions\GcError;

/**
 * Debug bar
 * 
 * @category   ZExt
 * @package    Debug
 * @subpackage DebugBar
 * @author     Mike.Mirten
 * @version    2.0beta
 */
class DebugBar {
	
	use OptionsTrait;
	
	const NAMESPACE_COLLECTORS = 'ZExt\Debug\Collectors';
	
	/**
	 * Collectors' locator
	 *
	 * @var LocatorByArgumentsInterface 
	 */
	protected $collectorsLocator;
	
	/**
	 * Information collectors instances
	 *
	 * @var CollectorInterface[]
	 */
	protected $collectors = [];
	
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
	 * Is profiles garbage collection enabled ?
	 *
	 * @var bool
	 */
	protected $profilesGcEnabled = true;
	
	/**
	 * Profiles lifetime for garbage collection in seconds
	 *
	 * @var int
	 */
	protected $profilesGcTime = 600;
	
	/**
	 * Max lifetime of the GC lock in seconds
	 *
	 * @var int
	 */
	protected $gcLockLifetime = 60;
	
	/**
	 * Content renderer
	 * 
	 * @var RendererInterface
	 */
	protected $renderer;
	
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
		
		$this->registerHandlers();
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
	 * Add the collectors
	 * 
	 * @param  array $collectors
	 * @return Debug
	 */
	public function addCollectors(array $collectors) {
		foreach ($collectors as $name => $collector) {
			if (is_string($name)) {
				$this->addCollector($collector, $name);
			} else {
				$this->addCollector($collector);
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
			if (! $profiler->isProfilerEnabled()) {
				$profiler->setProfilerStatus(true);
			}
			
			$profiler = $profiler->getProfiler();
		}
		
		if ($profiler instanceof ProfilerInterface) {
			if ($name === null && $profiler instanceof ProfilerExtendedInterface) {
				$name = $profiler->getName() . ' profiler';
			}
			
			$this->addCollector('profiler', $name, [
				'profiler' => $profiler
			]);
		}
		
		return $this;
	}
	
	/**
	 * Add the collector
	 * 
	 * @param  CollectorInterface | string $collector
	 * @param  string                      $name
	 * @param  array                       $params
	 * 
	 * @return DebugBar
	 */
	public function addCollector($collector, $name = null, array $params = null) {
		// Collector instance
		if ($collector instanceof CollectorInterface) {
			if ($name === null) {
				$class = get_class($collector);
				$name  = substr($class, strrpos($class, '\\') + 1);
			}
		}
		// String definition
		else if (is_string($collector)) {
			if ($name === null) {
				$name = $collector;
			}

			$collector = $this->loadCollector($collector, $params);
		}
		// Array definition
		else if (is_array($collector)) {
			$type = isset($collector['type']) ? $collector['type'] : $collector[0];

			// Define a name
			switch (true) {
				case $name !== null:
					break;

				case isset($collector['name']):
					$name = $collector['name'];
					break;

				case count($collector) > 2:
					$name = $collector[1];
					break;

				default:
					$name = $type;
			}

			// Define a params
			switch (true) {
				case isset($collector['params']):
					$collectorParams = $collector['params'];
					break;

				case count($collector) > 2:
					$collectorParams = $collector[2];
					break;

				default:
					$collectorParams = $collector[1];
			}

			if ($params !== null) {
				$collectorParams += $params;
			}

			$collector = $this->loadCollector($type, $collectorParams);
		}
		
		if ($collector !== null) {
			$this->collectors[$name] = $collector;
		}
		
		return $this;
	}
	
	/**
	 * Get the collector
	 * 
	 * @param  string $name
	 * @return CollectorInterface | null
	 */
	public function getCollector($name) {
		if (isset($this->collectors[$name])) {
			return $this->collectors[$name];
		}
	}
	
	/**
	 * Get all collectors
	 * 
	 * @return CollectorInterface[]
	 */
	public function getCollectors() {
		if (empty($this->collectors)) {
			$this->addDefaultCollectors();
		}
		
		return $this->collectors;
	}
	
	/**
	 * Has the collector ?
	 * 
	 * @param  string $name
	 * @return bool
	 */
	public function hasCollector($name) {
		return isset($this->collectors[$name]);
	}
	
	/**
	 * Add the default collectors
	 * 
	 * @return Debug
	 */
	public function addDefaultCollectors() {
		$this->addCollector('php', 'Php engine');
		$this->addCollector('time', 'Execution time');
		$this->addCollector('memory', 'Memory usage');
		$this->addCollector('files', 'Included files');
		$this->addCollector('errors', 'Occurred errors');
		$this->addCollector('request', 'Request info');
		$this->addCollector('response', 'Response info');
		
		return $this;
	}
	
	/**
	 * Load the collector
	 * 
	 * @return CollectorInterface | null
	 */
	protected function loadCollector($name, array $params = null) {
		$locator = $this->getCollectorsLocator();
		
		try {
			return $locator->getByArguments(ucfirst($name), [$params]);
		} catch (Exception $exception) {
			$this->pushException($exception);
		}
	}
	
	/**
	 * Get the collectors' locator
	 * 
	 * @return LocatorByArgumentsInterface
	 */
	public function getCollectorsLocator() {
		if ($this->collectorsLocator === null) {
			$locator = new InitializerNamespace();
			$locator->registerNamespace(self::NAMESPACE_COLLECTORS);
			
			$this->collectorsLocator = $locator;
		}
		
		return $this->collectorsLocator;
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
		
		return $this->renderDebugBar();
	}
	
	/**
	 * Render the loading script
	 * 
	 * @return string
	 */
	protected function renderLoadingScript() {
		$url = $this->deferredUrl . 'profile_' . $this->token . '.html';
		
		$script = $this->getAsset('deferred.js');
		$script = str_replace('$url', $url, $script);
		
		return (new Script($script))->render();
	}
	
	/**
	 * Render the debug bar
	 * 
	 * @return string
	 */
	protected function renderDebugBar() {
		$collectors = $this->getCollectors();
		$renderer   = $this->getRenderer();
		
		foreach ($collectors as $name => $collector) {
			try {
				$info = $collector->getInfo();
				$info->setName($name);
			} catch (Exception $exception) {
				$this->pushException($exception);
			}
			
			$renderer->addInfo($info);
		}
		
		return $renderer->render();
	}
	
	/**
	 * Set the content renderer
	 * 
	 * @param  RendererInterface $renderer
	 * @return DebugBar
	 */
	public function setRenderer(RendererInterface $renderer) {
		$this->renderer = $renderer;
		
		return $this;
	}
	
	/**
	 * Get the content renderer
	 * 
	 * @return RendererInterface
	 */
	public function getRenderer() {
		if ($this->renderer === null) {
			$this->renderer = new RendererHtml();
			$this->renderer->setAssetsPath(__DIR__ . DIRECTORY_SEPARATOR . 'Assets');
		}
		
		return $this->renderer;
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
	protected function registerHandlers() {
		set_exception_handler(function($exception) {
			$this->pushException($exception);
			
			echo $this->renderDebugBar();
		});
		
		register_shutdown_function(function() {
			if ($this->onShutdownCallback !== null) {
				$this->onShutdownCallback->__invoke($this);
			}
			
			// Debug bar rendering for the deferred mode
			if ($this->deferredMode) {
				// Garbage collection
				if ($this->profilesGcEnabled) {
					$this->gcCycle();
				}
				
				// Content creating
				$filePath = $this->deferredDir . DIRECTORY_SEPARATOR . 'profile_' . $this->token . '.html';
				if (file_put_contents($filePath, $this->renderDebugBar()) === false) {
					throw new InvalidPath('Unable to write the debug data into: "' . $filePath . '"');
				}
			}
		});
	}
	
	/**
	 * Push the exception into the debug bar
	 * 
	 * @param Exception $exception
	 */
	protected function pushException(Exception $exception) {
		$info = new Infoset();
			
		$info->setTitle('Exception')
		     ->setIcon('alert')
			 ->setLevel(Infoset::LEVEL_ALERT)
			 ->setContentType(Infoset::TYPE_DUMP)
			 ->pushContent($exception);

		$this->getRenderer()->addInfo($info);
	}
	
	/**
	 * Garbage collector
	 */
	protected function gcCycle() {
		$lockPath    = $this->deferredDir . DIRECTORY_SEPARATOR . '.zdebug_gclock';
		$currentTime = time();

		// Check the lock file and expiration time
		if (is_file($lockPath) && filemtime($lockPath) + $this->gcLockLifetime > $currentTime) {
			return;
		}

		if (! touch($lockPath)) {
			throw new GcError('Unable to create or update the lock file while a garbage collection cycle');
		}

		$profilesDir = new DirectoryIterator($this->deferredDir);
		
		foreach ($profilesDir as $file) {
			if (! $file->isFile()) {
				continue;
			}

			$fileName = $file->getFilename();

			if (strpos($fileName, 'profile_') !== 0) {
				continue;
			}

			if ($file->getMTime() + $this->profilesGcTime < $currentTime) {
				unlink($file->getRealPath());
			}
		}
	}
	
}