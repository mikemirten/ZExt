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

use ZExt\Filesystem\FileInterface;

use ZExt\Di\Config\Exceptions\InvalidConfigXml as InvalidConfig;

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
	 * @var ZExt\Filesystem\FileInterface
	 */
	protected $file;
	
	/**
	 * Includes definition
	 *
	 * @var array
	 */
	protected $includes;
	
	/**
	 * Services definitions
	 *
	 * @var object
	 */
	protected $services;
	
	/**
	 * Initializers definitions
	 *
	 * @var object
	 */
	protected $initializers;
	
	/**
	 * Override enabled
	 *
	 * @var bool
	 */
	protected $override;
	
	/**
	 * Constructor
	 * 
	 * @param FileInterface $file
	 */
	public function __construct(FileInterface $file) {
		$this->file = $file;
	}
	
	/**
	 * Initialize configuration
	 * 
	 * @throws InvalidConfig
	 */
	protected function initConfig() {
		$config = Xml::read($this->file);

		if ($config->getName() !== 'container') {
			throw new InvalidConfig('Root element of a config must be a "container"', null, null, $config);
		}

		$this->override = ($config->override === 'true');
		
		$this->includes     = [];
		$this->services     = new stdClass();
		$this->initializers = new stdClass();
		
		foreach ($config->getContent() as $element) {
			$name = $element->getName();
			
			if ($name === 'includes') {
				$this->includes = array_merge($this->includes, $this->processIncludes($element));
				continue;
			}
			
			if ($name === 'services') {
				$this->services = Std::objectMerge($this->services, $this->processServices($element));
				continue;
			}
			
			if ($name === 'initializers') {
				$this->initializers = Std::objectMerge($this->initializers, $this->processInitializers($element));
				continue;
			}
			
			throw new InvalidConfig('Unknown element "' . $element->getName() . '" in container', null, null, $element->getName());
		}
	}
	
	/**
	 * Gets includes
	 * 
	 * @return array
	 * @throws InvalidConfig
	 */
	public function getIncludes() {
		if ($this->includes === null) {
			$this->initConfig();
		}
		
		return $this->includes;
	}
	
	/**
	 * Gets definitions of services
	 * 
	 * @return object
	 * @throws InvalidConfig
	 */
	public function getServices() {
		if ($this->services === null) {
			$this->initConfig();
		}
		
		return $this->services;
	}
	
	/**
	 * Gets definitions of initializers
	 * 
	 * @return object
	 * @throws InvalidConfig
	 */
	public function getInitializers() {
		if ($this->initializers === null) {
			$this->initConfig();
		}
		
		return $this->initializers;
	}
	
	/**
	 * Process includes
	 * 
	 * @param  Element $includes
	 * @return array
	 */
	protected function ProcessIncludes(Element $includes) {
		$definitions = [];
		
		foreach ($includes->getContent() as $include) {
			if ($include->getName() === 'include') {
				if (isset($include->load)) {
					$definitions[] = $include->load;
					continue;
				}
				
				$content = $include->getValue();
				
				if (! empty($content)) {
					$definitions[] = $content;
					continue;
				}
				
				throw new InvalidConfig('Include must contain value or "load" attribute', null, null, $include);
			}
			
			throw new InvalidConfig('Unknown element "' . $include->getName() . '" in includes', null, null, $include);
		}
		
		return $definitions;
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
					throw new InvalidConfig('Service definition must contain an ID of service', null, null, $service);
				}
				
				if (! $this->override && isset($definitions->{$service->id})) {
					throw new InvalidConfig('Service "' . $service->id . '" is already been set and cannot be overridden', null, null, $service);
				}
				
				$definitions->{$service->id} = $this->processService($service, $namespace);
				continue;
			}
			
			throw new InvalidConfig('Unknown element "' . $service->getName() . '" in services', null, null, $service);
		}
		
		return $definitions;
	}
	
	/**
	 * Process service
	 * 
	 * @param  Element $service
	 * @param  string  $namespace
	 * @return object
	 * @throws InvalidConfig
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
	 * @throws InvalidConfig
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
					throw new InvalidConfig('Initializer "' . $id . '" is already been set and cannot be overridden');
				}
				
				$definitions->$id = $initializerDefinition;
				continue;
			}
			
			throw new InvalidConfig('Unknown element "' . $initializers->getName() . '" in initializers', null, null, $initializer);
		}
		
		return $definitions;
	}
	
	/**
	 * Process initializer
	 * 
	 * @param  Element $initializer
	 * @return object
	 * @throws InvalidConfig
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
			
			throw new InvalidConfig('Unknown element "' . $param->getName() . '" in service devinition', null, null, $param);
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
			
			throw new InvalidConfig('Unknown element "' . $arg->getName() . '" in service devinition', null, null, $arg);
		}
	}
	
	/**
	 * Process argument
	 * 
	 * @param  Element $arg
	 * @return stdClass
	 * @throws InvalidConfig
	 */
	protected function processArgument(Element $arg) {
		// Value of the special types:
		if (isset($arg->type)) {
			$type = strtolower(trim($arg->type));
			
			switch ($type) {
				case 'bool':
				case 'boolean':
					return $this->processArgumentBoolean($arg);
					
				case 'service':
					return $this->processArgumentService($arg);
					
				case 'null':
					return $this->processArgumentNull($arg);
					
				case 'array':
					return $this->processArgumentArray($arg);
			}
			
			throw new InvalidConfig('Invalid type of argument: "' . $type . '"', null, null, $arg);
		}
		
		// Scalar value: string, int, float
		$definition = new stdClass();
		
		if (isset($arg->value)) {
			$definition->type  = 'value';
			$definition->value = Std::parseValue($arg->value);
			
			return $definition;
		}
		
		$value = $arg->getValue();
		
		if (! empty($value)) {
			$definition->type  = 'value';
			$definition->value = Std::parseValue($value);
			
			return $definition;
		}
		var_dump($arg->toXML()); die;
		throw new InvalidConfig('Invalid definition of argument', null, null, $arg);
	}
	
	/**
	 * Process "Service" type argument
	 * 
	 * @param  Element $arg
	 * @return stdClass
	 * @throws InvalidConfig
	 */
	protected function processArgumentService(Element $arg) {
		$definition = new stdClass();
		$definition->type = 'service';
		
		if (! isset($arg->id)) {
			throw new InvalidConfig('Definition of an argument type "service" must contain the "id" attribute', null, null, $arg);
		}
		
		$definition->id = trim($arg->id);
		
		$content = $arg->getContent();
			
		if (! empty($content)) {
			$this->processParameters($content, $definition);
		}
		
		return $definition;
	}
	
	/**
	 * Process "Boolean" type argument
	 * 
	 * @param  Element $arg
	 * @return stdClass
	 * @throws InvalidConfig
	 */
	protected function processArgumentBoolean(Element $arg) {
		$definition = new stdClass();
		$definition->type = 'value';
		
		if (isset($arg->value)) {
			$definition->value = (trim($arg->value) === 'true');
			return $definition;
		}
		
		$value = $arg->getValue();
		
		if (! empty($value)) {
			$definition->value = (trim($value) === 'true');
			return $definition;
		}
		
		throw new InvalidConfig('Definition of an argument type "boolean" must contain a value as content or the "value" attribute', null, null, $arg);
	}
	
	/**
	 * Process "Null" type argument
	 * 
	 * @param  Element $arg
	 * @return stdClass
	 * @throws InvalidConfig
	 */
	protected function processArgumentNull(Element $arg) {
		$definition = new stdClass();
		
		$definition->type  = 'value';
		$definition->value = null;
		
		return $definition;
	}
	
	/**
	 * Process "Null" type argument
	 * 
	 * @param  Element $arg
	 * @return stdClass
	 * @throws InvalidConfig
	 */
	protected function processArgumentArray(Element $arg) {
		$definition = new stdClass();
		
		$definition->type  = 'value';
		$definition->value = [];
				
		foreach ($arg->getContent() as $element) {
			if ($element->getName() !== 'element') {
				throw new InvalidConfig('Array definition must contain only "element" elements', null, null, $arg);
			}
			
			if (isset($element->key)) {
				$definition->value[$element->key] = $this->processArgument($element);
			} else {
				$definition->value[] = $this->processArgument($element);
			}
		}
		
		return $definition;
	}
	
	/**
	 * Get unique ID of reader
	 * 
	 * @return string
	 */
	public function getId() {
		return $this->file->getRealpath();
	}
	
}