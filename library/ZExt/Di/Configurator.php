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

use ZExt\Di\Config\Exceptions\InvalidConfig;
use ZExt\Di\Definition\Argument\ServiceReferenceArgument;

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
	 * Configs
	 *
	 * @var Config\ReaderInterface[]
	 */
	protected $configs = [];
	
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
	protected $overridingEnabled = false;
	
	/**
	 * Constructor
	 * 
	 * @param Config\ReaderInterface | array $config
	 */
	public function __construct($config, ContainerInterface $container) {
		$this->addConfig($config);
		
		$this->container = $container;
	}
	
	/**
	 * Add config
	 * 
	 * @param  Config\ReaderInterface | array $config
	 * @return Configurator
	 */
	public function addConfig($config) {
		if (! $config instanceof Config\ReaderInterface && ! is_array($config)) {
			throw new InvalidConfig('Config must be an instance of "ZExt\Di\Config\ReaderInterface" or an array, "' . gettype($config) . '" given.');
		}
		
		$this->configs[] = $config;
		
		return $this;
	}
	
	/**
	 * Enable services overriding
	 * 
	 * @return Configurator
	 */
	public function enableOverriding() {
		$this->overridingEnabled = true;
		
		return $this;
	}
	
	/**
	 * Is services overriding enabled ?
	 * 
	 * @return bool
	 */
	public function isOverridingEnabled() {
		return $this->overridingEnabled;
	}
	
	/**
	 * Configure container
	 * 
	 * @return ContainerInterface
	 * @throws Exceptions\ServiceOverride
	 * @throws InvalidConfig
	 */
	public function configure() {
		$config = $this->mergeConfigs($this->configs);
		
		if (isset($config->services)) {
			$this->applyServices($config->services);
		}
		
		if (isset($config->initializers)) {
			$this->applyInitializers($config->initializers);
		}
		
		return $this->container;
	}
	
	/**
	 * Apply config to container
	 * 
	 * @param array $config
	 */
	protected function applyServices(array $config) {
		foreach ($config as $definitionConf) {
			if (! isset($definitionConf->type)) {
				throw new InvalidConfig('Service definition must contain a "type" property"');
			}
			
			if ($definitionConf->type === 'class') {
				if (! isset($definitionConf->class)) {
					throw new InvalidConfig('Service definition of type "class" must contain a "class" property');
				}
				
				$definition = new Definition\ClassDefinition($definitionConf->class);
				
				if (! empty($definitionConf->factory)) {
					$definition->setFactoryMode();
				}
				
				if (isset($definitionConf->arguments)) {
					$args = $this->processArguments($definitionConf->arguments);
					
					$definition->setArguments($args);
				}
				
				$this->container->set($definitionConf->id, $definition);
				continue;
			}
			
			throw new InvalidConfig('Unknown type of service: "' . $definitionConf->type . '"');
		}
	}
	
	protected function applyInitializers(array $config) {
		foreach ($config as $definitionConf) {
			if (! isset($definitionConf->type)) {
				throw new InvalidConfig('Service definition must contain a "type" property"');
			}
			
			if ($definitionConf->type === 'namespace') {
				if (! isset($definitionConf->namespace)) {
					throw new InvalidConfig('Initializer definition of type "namespace" must contain a "namespace" property');
				}
				
				$initializer = new InitializerNamespace($definitionConf->namespace);
			}
			else if ($definitionConf->type === 'object') {
				if (! isset($definitionConf->class)) {
					throw new InvalidConfig('Initializer definition of type "object" must contain a "class" property');
				}
				
				$initializer = new $definitionConf->class();
			}
			else {
				throw new InvalidConfig('Unknown type of initializer: "' . $definitionConf->type . '"');
			}
			
			if (! empty($definitionConf->factory)) {
				$initializer->setFactoryMode();
			}

			if (isset($definitionConf->arguments)) {
				$args = $this->processArguments($definitionConf->arguments);

				$initializer->setArguments($args);
			}

			$this->container->addLocator($initializer);
		}
	}
	
	/**
	 * Process arguments
	 * 
	 * @param  array $arguments
	 * @return array
	 * @throws InvalidConfig
	 */
	protected function processArguments(array $arguments) {
		$processedArgs = [];
		
		foreach ($arguments as $argument) {
			if (! isset($argument->type)) {
				throw new InvalidConfig('Argument definition must contain a "type" property"');
			}
			
			if ($argument->type === 'value') {
				if (! isset($argument->value)) {
					throw new InvalidConfig('Argument of type "value" must contain a "value" property');
				}
				
				$processedArgs[] = $argument->value;
				continue;
			}
			
			if ($argument->type === 'service') {
				if (! isset($argument->id)) {
					throw new InvalidConfig('Argument of type "service" must contain an "id" property');
				}
				
				$reference = new ServiceReferenceArgument($this->container, $argument->id);
				
				if (isset($argument->arguments)) {
					$args = $this->processArguments($argument->arguments);
					
					$reference->setArguments($args);
				}
				
				$processedArgs[] = $reference;
				continue;
			}
			
			throw new InvalidConfig('Unknown type of argument: "' . $argument->type . '"');
		}
		
		return $processedArgs;
	}
	
	/**
	 * Merge configs into one
	 * 
	 * @param  array $configs
	 * @return array
	 * @throws Exceptions\ServiceOverride
	 * @throws InvalidConfig
	 */
	protected function mergeConfigs(array $configs) {
		$services     = [];
		$initializers = [];
		
		foreach ($configs as $config) {
			if ($config instanceof Config\ReaderInterface) {
				$config = $config->getConfiguration();
			}
			
			if (isset($config->services)) {
				foreach ($config->services as $definition) {
					if (! isset($definition->id)) {
						throw new InvalidConfig('Service definition must contain an "id" property');
					}

					if (! $this->overridingEnabled && isset($services[$definition->id])) {
						throw new Exceptions\ServiceOverride('Double definition for the service "' . $definition->id . '"');
					}

					$services[$definition->id] = $definition;
				}
			}
			
			if (isset($config->initializers)) {
				foreach ($config->initializers as $initializer) {
					$initializers[] = $initializer;
				}
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