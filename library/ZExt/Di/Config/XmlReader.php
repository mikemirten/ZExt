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

namespace ZExt\Di\Config;

use ZExt\Xml\Xml,
    ZExt\Xml\Element;

use ZExt\Components\Std;

use ZExt\File\FileInterface,
    ZExt\File\File;	

use stdClass;

/**
 * XML Configuration reader
 * 
 * @category   ZExt
 * @package    Di
 * @subpackage Config
 * @author     Mike.Mirten
 * @version    1.0
 */
class XmlReader implements ReaderInterface {
	
	/**
	 * Path to config
	 *
	 * @var ZExt\File\FileInterface
	 */
	protected $file;
	
	/**
	 * Config
	 *
	 * @var Element 
	 */
	protected $config;
	
	/**
	 * Override enabled
	 *
	 * @var bool
	 */
	protected $override;
	
	/**
	 * Constructor
	 * 
	 * @param FileInterface | string $file
	 */
	public function __construct($file) {
		if ($file instanceof FileInterface) {
			$this->file = $file;
		} else {
			$this->file = new File($file);
		}
	}
	
	/**
	 * Get configuration content
	 * 
	 * @return Element
	 * @throws Exceptions\InvalidConfig
	 */
	protected function getContent() {
		if ($this->config === null) {
			$this->config = Xml::read($this->file);

			if ($this->config->getName() !== 'container') {
				throw new Exceptions\InvalidConfig('Root element of a config must be a "container"');
			}
			
			$this->override = ($this->config->override === 'true');
		}
		
		return $this->config;
	}
	
	/**
	 * Gets definitions of services
	 * 
	 * @return object
	 * @throws Exceptions\InvalidConfig
	 */
	public function getConfiguration() {
		$services     = new stdClass();
		$initializers = new stdClass();
		
		foreach ($this->getContent()->getContent() as $element) {
			$name = $element->getName();
			
			if ($name === 'services') {
				$services = Std::objectMerge($services, $this->processServices($element));
				continue;
			}
			
			if ($name === 'initializers') {
				$initializers = Std::objectMerge($initializers, $this->processInitializers($element));
				continue;
			}
			
			throw new Exceptions\InvalidConfig('Unknown element "' . $element->getName() . '" in container');
		}
		
		$configuration = new stdClass();
		
		if (! empty($services)) {
			$configuration->services = $services;
		}
		
		if (! empty($services)) {
			$configuration->initializers = $initializers;
		}
		
		return $configuration;
	}
	
	/**
	 * Process services
	 * 
	 * @param  Element $services
	 * @return object
	 */
	protected function processServices(Element $services) {
		$definitions = new stdClass();
		
		$namespace = isset($services->namespace) ? $services->namespace : null;
		
		foreach ($services->getContent() as $service) {
			if ($service->getName() === 'service') {
				if (! isset($service->id)) {
					throw new Exceptions\InvalidConfig('Service definition must contain an ID of service');
				}
				
				if (! $this->override && isset($definitions->{$service->id})) {
					throw new Exceptions\InvalidConfig('Service "' . $service->id . '" is already been set and cannot be overridden');
				}
				
				$definitions->{$service->id} = $this->processService($service, $namespace);
				continue;
			}
			
			throw new Exceptions\InvalidConfig('Unknown element "' . $service->getName() . '" in services');
		}
		
		return $definitions;
	}
	
	/**
	 * Process service
	 * 
	 * @param  Element $service
	 * @param  string  $namespace
	 * @return object
	 * @throws Exceptions\InvalidConfig
	 */
	protected function processService(Element $service, $namespace = null) {
		$definition = new stdClass();
		
		if (isset($service->class)) {
			$definition->type  = 'class';
			$definition->class = ($namespace === null)
				? $service->class
				: $namespace . '\\' . $service->class;
		}
		
		if ($service->factory === 'true') {
			$definition->factory = true;
		}
		
		$content = $service->getContent();
		
		if (! empty($content)) {
			$this->processParameters($content, $definition);
		}
		
		return $definition;
	}
	
	/**
	 * Process initializers
	 * 
	 * @param  Element $initializers
	 * @return array
	 * @throws Exceptions\InvalidConfig
	 */
	protected function processInitializers(Element $initializers) {
		$definitions = new stdClass();
		
		foreach ($initializers->getContent() as $initializer) {
			if ($initializer->getName() === 'initializer') {
				$initializerDefinition = $this->processInitializer($initializer);
				
				$id = isset($initializer->id)
					? $initializer->id
					: substr(md5(json_encode($initializerDefinition)), 24);
				
				if (! $this->override && isset($definitions->$id)) {
					throw new Exceptions\InvalidConfig('Initializer "' . $id . '" is already been set and cannot be overridden');
				}
				
				$definitions->$id = $initializerDefinition;
				continue;
			}
			
			throw new Exceptions\InvalidConfig('Unknown element "' . $initializers->getName() . '" in initializers');
		}
		
		return $definitions;
	}
	
	/**
	 * Process initializer
	 * 
	 * @param  Element $initializer
	 * @return object
	 * @throws Exceptions\InvalidConfig
	 */
	protected function processInitializer(Element $initializer) {
		$definition = new stdClass();
		
		if (isset($initializer->namespace)) {
			$definition->type      = 'namespace';
			$definition->namespace = $initializer->namespace;
		}
		else if (isset($initializer->class)) {
			$definition->type  = 'object';
			$definition->class = $initializer->class;
		}
		
		if ($initializer->factory === 'true') {
			$definition->factory = true;
		}
		
		$content = $initializer->getContent();
		
		if (! empty($content)) {
			$this->processParameters($content, $definition);
		}
		
		return $definition;
	}

	/**
	 * Process parameters
	 * 
	 * @param array    $params
	 * @param stdClass $definition
	 */
	protected function processParameters(array $params, stdClass $definition) {
		foreach ($params as $param) {
			if ($param->getName() === 'arguments') {
				$content = $param->getContent();
				
				if (! empty($content)) {
					$this->processArguments($content, $definition);
				}
				continue;
			}
			
			throw new Exceptions\InvalidConfig('Unknown element "' . $arg->getName() . '" in service devinition');
		}
	}
	
	/**
	 * Process arguments
	 * 
	 * @param array    $args
	 * @param stdClass $definition
	 */
	protected function processArguments(array $args, stdClass $definition) {
		foreach ($args as $arg) {
			if ($arg->getName() === 'argument') {
				if (! isset($definition->arguments)) {
					$definition->arguments = [];
				}
				
				$definition->arguments[] = $this->processArgument($arg);
				continue;
			}
			
			throw new Exceptions\InvalidConfig('Unknown element "' . $arg->getName() . '" in service devinition');
		}
	}
	
	/**
	 * Process argument
	 * 
	 * @param  Element $arg
	 * @return stdClass
	 * @throws Exceptions\InvalidConfig
	 */
	protected function processArgument(Element $arg) {
		$definition = new stdClass();
		
		if (isset($arg->id)) {
			$definition->type = 'service';
			$definition->id   = $arg->id;
			
			$content = $arg->getContent();
			
			if (! empty($content)) {
				$this->processParameters($content, $definition);
			}
			
			return $definition;
		}
		
		if ($arg->type === 'boolean') {
			$definition->type  = 'value';
			$definition->value = (trim($arg->value) === 'true');
			return $definition;
		}
		
		if (isset($arg->value)) {
			$definition->type  = 'value';
			$definition->value = Std::parseValue($arg->value);
			return $definition;
		}
		
		if ($arg->type === 'array') {
			$definition->type  = 'value';
			$definition->value = $this->processArray($arg);
			return $definition;
		}
		
		if ($arg->type === 'null') {
			$definition->type  = 'value';
			$definition->value = null;
			return $definition;
		}
		
		throw new Exceptions\InvalidConfig('Invalid definition of argument');
	}
	
	/**
	 * Process array
	 * 
	 * @param  Element $source
	 * @return array
	 * @throws Exceptions\InvalidConfig
	 */
	protected function processArray(Element $source) {
		$array = [];
		
		foreach ($source->getContent() as $element) {
			$name = $element->getName();
			
			if ($name === 'element') {
				if (isset($element->key)) {
					$array[$element->key] = $this->processArgument($element);
				} else {
					$array[] = $this->processArgument($element);
				}
				
				continue;
			}
			
			if ($name === 'value') {
				$definition = new stdClass();
				
				$definition->type  = 'value';
				$definition->value = Std::parseValue($element->getValue());
				
				if (isset($element->key)) {
					$array[$element->key] = $definition;
				} else {
					$array[] = $definition;
				}
				
				continue;
			}
			
			throw new Exceptions\InvalidConfig('Array definition must contain only "element" or "value" elements');
		}
		
		return $array;
	}
	
}