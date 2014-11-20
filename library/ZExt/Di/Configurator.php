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

namespace ZExt\Di;

use ZExt\Di\Definition\Argument\ServiceReferenceArgument;
use ZExt\Filesystem\DirectoryInterface;
use ZExt\Filesystem\FileInterface;

use stdClass;

/**
 * Configurator of dependency injection container
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Container
 * @author     Mike.Mirten
 * @version    1.0
 */
class Configurator {
	
	/**
	 * Directory with configs
	 * 
	 * @var DirectoryInterface 
	 */
	protected $configsDir;
	
	/**
	 * Content of configuration
	 *
	 * @var Config\ReaderInterface[]
	 */
	protected $configReaders = [];
	
	/**
	 * Services' container
	 *
	 * @var ContainerInterface
	 */
	protected $container;
	
	/**
	 * Overriding enabled
	 *
	 * @var bool
	 */
	protected $override = false;
	
	/**
	 * Constructor
	 * 
	 * @param ContainerInterface $container
	 * @param DirectoryInterface $configsDir
	 */
	public function __construct(ContainerInterface $container, DirectoryInterface $configsDir = null) {
		$this->container  = $container;
		$this->configsDir = $configsDir;
	}
	
	/**
	 * Enable services overriding
	 * 
	 * @return Configurator
	 */
	public function enableOverriding() {
		$this->override = true;
		
		return $this;
	}
	
	/**
	 * Is services overriding enabled ?
	 * 
	 * @return bool
	 */
	public function isOverridingEnabled() {
		return $this->override;
	}
	
	/**
	 * Add configuration reader
	 * 
	 * @param  Config\ReaderInterface $config
	 * @return Configurator
	 */
	public function addConfig(Config\ReaderInterface $config) {
		$this->configReaders[] = $config;
		
		return $this;
	}
	
	/**
	 * Load configuration file
	 * 
	 * @param  string $config
	 * @return Configurator
	 * @throws Exceptions\InvalidConfig
	 */
	public function load($config) {
		if (isset($this->configReaders[$config])) {
			throw new Exceptions\InvalidConfig('Configuration file "' . $config . '" already been loaded');
		}
		
		if ($this->configsDir === null) {
			new Exceptions\ConfiguratorError('Directory of configuration files did not been set');
		}
		
		$file = $this->configsDir->getFile($config);
		
		$this->configReaders[$config] = $this->initReader($file);
		
		return $this;
	}
	
	/**
	 * Load reader by config file
	 * 
	 * @param  FileInterface $file
	 * @return Config\ReaderInterface;
	 * @throws Exceptions\InvalidConfig
	 */
	protected function initReader(FileInterface $file) {
		$extension = $file->getExtension();
		
		if ($extension === 'xml') {
			return new Config\XmlReader($file);
		}
		
		throw new Exceptions\InvalidConfig('Unknown type of configuration "' . $extension . '"');
	}
	
	/**
	 * Configure container
	 * 
	 * @return ContainerInterface
	 * @throws Exceptions\ServiceOverride
	 * @throws Exceptions\InvalidConfig
	 */
	public function configure() {
		$config = $this->mergeConfigs($this->configReaders);
		
		if (isset($config->services)) {
			$this->applyServices($config->services);
		}
		
		if (isset($config->initializers)) {
			$this->applyInitializers($config->initializers);
		}
		
		return $this->container;
	}
	
	/**
	 * Apply services config to container
	 * 
	 * @param  object $config
	 * @throws Exceptions\InvalidConfig
	 */
	protected function applyServices(stdClass $config) {
		foreach ($config as $id => $definitionConf) {
			if (! isset($definitionConf->type)) {
				throw new Exceptions\InvalidConfig('Service definition must contain a "type" property"');
			}
			
			if ($definitionConf->type === 'class') {
				if (! isset($definitionConf->class)) {
					throw new Exceptions\InvalidConfig('Service definition of type "class" must contain a "class" property');
				}
				
				$definition = new Definition\ClassDefinition($definitionConf->class);
				
				if (! empty($definitionConf->factory)) {
					$definition->setFactoryMode();
				}
				
				if (isset($definitionConf->arguments)) {
					$args = $this->processArguments($definitionConf->arguments);
					
					$definition->setArguments($args);
				}
				
				$this->container->set($id, $definition);
				continue;
			}
			
			throw new Exceptions\InvalidConfig('Unknown type of service: "' . $definitionConf->type . '"');
		}
	}
	
	/**
	 * Apply initializers config to container
	 * 
	 * @param  object $config
	 * @throws Exceptions\InvalidConfig
	 */
	protected function applyInitializers(stdClass $config) {
		foreach ($config as $id => $definitionConf) {
			if (! isset($definitionConf->type)) {
				throw new Exceptions\InvalidConfig('Service definition must contain a "type" property"');
			}
			
			if ($definitionConf->type === 'namespace') {
				if (! isset($definitionConf->namespace)) {
					throw new Exceptions\InvalidConfig('Initializer definition of type "namespace" must contain a "namespace" property');
				}
				
				$initializer = new InitializerNamespace($definitionConf->namespace);
			}
			else if ($definitionConf->type === 'object') {
				if (! isset($definitionConf->class)) {
					throw new Exceptions\InvalidConfig('Initializer definition of type "object" must contain a "class" property');
				}
				
				$initializer = new $definitionConf->class();
			}
			else {
				throw new Exceptions\InvalidConfig('Unknown type of initializer: "' . $definitionConf->type . '"');
			}
			
			if (! empty($definitionConf->factory)) {
				$initializer->setFactoryMode();
			}

			if (isset($definitionConf->arguments)) {
				$args = $this->processArguments($definitionConf->arguments);

				$initializer->setArguments($args);
			}

			$this->container->addLocator($initializer, $id);
		}
	}
	
	/**
	 * Process arguments
	 * 
	 * @param  array $arguments
	 * @return array
	 * @throws Exceptions\InvalidConfig
	 */
	protected function processArguments(array $arguments) {
		$processedArgs = [];
		
		foreach ($arguments as $argument) {
			if (! isset($argument->type)) {
				throw new Exceptions\InvalidConfig('Argument definition must contain a "type" property"');
			}
			
			if ($argument->type === 'value') {
				if (! isset($argument->value)) {
					throw new Exceptions\InvalidConfig('Argument of type "value" must contain a "value" property');
				}
				
				if (is_array($argument->value)) {
					$processedArgs[] = $this->processArguments($argument->value);
					continue;
				}
				
				$processedArgs[] = $argument->value;
				continue;
			}
			
			if ($argument->type === 'service') {
				if (! isset($argument->id)) {
					throw new Exceptions\InvalidConfig('Argument of type "service" must contain an "id" property');
				}
				
				$reference = new ServiceReferenceArgument($this->container, $argument->id);
				
				if (isset($argument->arguments)) {
					$args = $this->processArguments($argument->arguments);
					
					$reference->setArguments($args);
				}
				
				$processedArgs[] = $reference;
				continue;
			}
			
			throw new Exceptions\InvalidConfig('Unknown type of argument: "' . $argument->type . '"');
		}
		
		return $processedArgs;
	}
	
	/**
	 * Merge configs into one
	 * 
	 * @param  array $configs
	 * @return object
	 * @throws Exceptions\ServiceOverride
	 */
	protected function mergeConfigs(array $configs) {
		$services     = new stdClass();
		$initializers = new stdClass();
		
		foreach ($configs as $config) {
			foreach ($config->getServices() as $id => $service) {
				if (! $this->override && isset($services->$id)) {
					throw new Exceptions\ServiceOverride('Service "' . $id . '" is already been set and cannot be overridden');
				}

				$services->$id = $service;
			}
			
			foreach ($config->getInitializers() as $id => $initializer) {
				if (! $this->override && isset($initializer->$id)) {
					throw new Exceptions\ServiceOverride('Initializer "' . $id . '" is already been set and cannot be overridden');
				}

				$initializers->$id = $initializer;
			}
		}
		
		$merged = new stdClass();
		
		if (! empty($services)) {
			$merged->services = $services;
		}
		
		if (! empty($initializers)) {
			$merged->initializers = $initializers;
		}
		
		return $merged;
	}
	
}