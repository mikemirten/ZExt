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

use ZExt\Di\Definition\Argument\ServiceReferenceArgument as ServiceReference,
    ZExt\Di\Definition\Argument\ConfigReferenceArgument as ConfigReference;

use ZExt\Filesystem\DirectoryInterface,
    ZExt\Filesystem\FileInterface;

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
	 * @param  Config\ReaderInterface $reader
	 * @return Configurator
	 */
	public function addConfigReader(Config\ReaderInterface $reader) {
		$id = $reader->getId();
		
		if (isset($this->configReaders[$id])) {
			throw new Exceptions\InvalidConfig('Configuration reader with ID "' . $id . '" already exists');
		}
		
		$this->configReaders[] = $reader;
		
		foreach ($reader->getIncludes() as $include) {
			$this->load($include);
		}
		
		return $this;
	}
	
	/**
	 * Get config readers
	 * 
	 * @return Config\ReaderInterface[]
	 */
	public function getConfigReaders() {
		return $this->configReaders;
	}
	
	/**
	 * Load configuration file
	 * 
	 * @param  string $config
	 * @return Configurator
	 * @throws Exceptions\InvalidConfig
	 */
	public function load($config) {
		if ($this->configsDir === null) {
			new Exceptions\ConfiguratorError('Directory of configuration files did not been set');
		}
		
		$file = $this->configsDir->getFile($config);
		
		$this->addConfigReader($this->initReader($file));
		
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
		
		if (isset($config->parameters)) {
			$this->applyParameters($config->parameters);
		}
		
		if (isset($config->services)) {
			$this->applyServices($config->services);
		}
		
		if (isset($config->initializers)) {
			$this->applyInitializers($config->initializers);
		}
		
		return $this->container;
	}
	
	/**
	 * Apply parameters to container
	 * 
	 * @param stdClass $definitions
	 */
	protected function applyParameters(stdClass $definitions) {
		$parameters = $this->processArguments($definitions);
		$config     = $this->container->getParametersConfig();
		
		foreach ($parameters as $name => $value) {
			$config->set($name, $value);
		}
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
	 * @param  array | object $arguments
	 * @return array
	 * @throws Exceptions\InvalidConfig
	 */
	protected function processArguments($arguments) {
		$processedArgs = [];
		
		foreach ($arguments as $key => $argument) {
			if (! isset($argument->type)) {
				throw new Exceptions\InvalidConfig('Argument definition must contain a "type" property"');
			}
			
			$type = strtolower(trim($argument->type));
			
			if ($type === 'value') {
				$processedArgs[$key] = $this->processArgumentValue($argument);
				continue;
			}
			
			if ($type === 'service') {
				$processedArgs[$key] = $this->processArgumentService($argument);
				continue;
			}
			
			if ($type === 'parameter') {
				$processedArgs[$key] = $this->processArgumentParameter($argument);
				continue;
			}
			
			throw new Exceptions\InvalidConfig('Unknown type of argument: "' . $type . '"');
		}
		
		return $processedArgs;
	}
	
	/**
	 * Process argument of the type "value"
	 * 
	 * @param  stdClass $argument
	 * @return mixed
	 * @throws Exceptions\InvalidConfig
	 */
	protected function processArgumentValue(stdClass $argument) {
		if (! isset($argument->value)) {
			throw new Exceptions\InvalidConfig('Argument of type "value" must contain a "value" property');
		}

		if (is_array($argument->value)) {
			return $this->processArguments($argument->value);
		}

		return $argument->value;
	}
	
	/**
	 * Process argument of the type "service"
	 * 
	 * @param  stdClass $argument
	 * @return mixed
	 * @throws Exceptions\InvalidConfig
	 */
	protected function processArgumentService(stdClass $argument) {
		if (! isset($argument->id)) {
			throw new Exceptions\InvalidConfig('Argument of type "service" must contain an "id" property');
		}

		$reference = new ServiceReference($this->container, $argument->id);

		if (isset($argument->arguments)) {
			$args = $this->processArguments($argument->arguments);

			$reference->setArguments($args);
		}

		return $reference;
	}
	
	/**
	 * Process argument of the type "service"
	 * 
	 * @param  stdClass $argument
	 * @return mixed
	 * @throws Exceptions\InvalidConfig
	 */
	protected function processArgumentParameter(stdClass $argument) {
		if (! isset($argument->name)) {
			throw new Exceptions\InvalidConfig('Argument of type "parameter" must contain a "name" property');
		}
		
		$config = $this->container->getParametersConfig();
		$name   = trim($argument->name);
		
		if (! empty($argument->deferred)) {
			return new ConfigReference($config, $name);
		}
		
		if (! $config->has($name)) {
			throw new Exceptions\InvalidConfig('Config contains no "' . $name . '" parameter');
		}
		
		return $config->get($name);
	}
	
	/**
	 * Merge configs into one
	 * 
	 * @param  array $configs
	 * @return object
	 * @throws Exceptions\ServiceOverride
	 */
	protected function mergeConfigs(array $configs) {
		$result = new stdClass();
		
		$result->parameters   = new stdClass();
		$result->services     = new stdClass();
		$result->initializers = new stdClass();
		
		foreach ($configs as $config) {
			$this->mergeConfigsPart($config->getParameters(), $result->parameters);
			$this->mergeConfigsPart($config->getServices(), $result->services);
			$this->mergeConfigsPart($config->getInitializers(), $result->initializers);
		}
		
		return $result;
	}
	
	/**
	 * Merge part of config
	 * 
	 * @param  stdClass $config
	 * @param  stdClass $destination
	 * @throws Exceptions\ServiceOverride
	 */
	protected function mergeConfigsPart(stdClass $config, stdClass $destination) {
		foreach ($config as $id => $value) {
			if (! $this->override && isset($destination->$id)) {
				throw new Exceptions\ServiceOverride('ID "' . $id . '" is already been set and cannot be overridden');
			}

			$destination->$id = $value;
		}
	}
	
}