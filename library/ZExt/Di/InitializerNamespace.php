<?php
namespace ZExt\Di;

use ZExt\Di\Exception\NoService,
    ZExt\Di\Exception\NoNamespaces;

use ZExt\Log\LoggerAwareInterface,
    ZExt\Log\LoggerAwareTrait;

use ZExt\Config\ConfigAwareInterface,
    ZExt\Config\ConfigAwareTrait;

use Closure;

class InitializerNamespace

	implements InitializerInterface,
	           LocatorInterface,
	           LocatorAwareInterface,
	           LoggerAwareInterface,
	           ConfigAwareInterface {
	
	use LocatorAwareTrait;
	use LoggerAwareTrait;
	use ConfigAwareTrait;
	
	/**
	 * Services' namespaces
	 *
	 * @var string[]
	 */
	protected $_namespaces = [];
	
	/**
	 * Initialized services
	 *
	 * @var array
	 */
	protected $_services = [];
	
	/**
	 * Service's class prefix
	 *
	 * @var string
	 */
	protected $_classPrefix = '';
	
	/**
	 * Service's class postfix
	 *
	 * @var string
	 */
	protected $_classPostfix = '';
	
	/**
	 * Use separate directory for an each service
	 *
	 * @var bool
	 */
	protected $_dirForEachService = false;
	
	/**
	 * On init callback
	 * 
	 * @var Closure
	 */
	protected $_onInit;
	
	/**
	 * Misses while class loading
	 *
	 * @var int
	 */
	protected $_loadMisses = 0;
	
	/**
	 * Prefix for the config's service id
	 *
	 * @var string
	 */
	protected $_configIdPrefix = '';
	
	/**
	 * Postfix for the config's service id
	 * 
	 * @var string 
	 */
	protected $_configIdPostfix = 'Config';
	
	/**
	 * Set a prefix for the config's service id
	 * 
	 * @param  string $prefix
	 * @return InitializerNamespace
	 */
	public function setConfigIdPrefix($prefix) {
		$this->_configIdPrefix = (string) $prefix;
		
		return $this;
	}

	/**
	 * Set a postfix for the config's service id
	 * 
	 * @param  string $postfix
	 * @return InitializerNamespace
	 */
	public function setConfigIdPostfix($postfix) {
		$this->_configIdPostfix= (string) $postfix;
		
		return $this;
	}

	/**
	 * Set prefix for a services' classes
	 * 
	 * @param string $prefix
	 * @return Initializer
	 */
	public function setClassPrefix($prefix) {
		$this->_classPrefix = (string) $prefix;
		
		return $this;
	}
	
	/**
	 * Set postfix for a services' classes
	 * 
	 * @param string $postfix
	 * @return Initializer
	 */
	public function setClassPostfix($postfix) {
		$this->_classPostfix = (string) $postfix;
		
		return $this;
	}
	
	/**
	 * Calls right after an object instantiation
	 * 
	 * @param  Closure $callback
	 * @return Initializer
	 */
	public function setOnInit(Closure $callback) {
		$this->_onInit = $callback;
		
		return $this;
	}
	
	/**
	 * Set using a separate directory for an each service
	 * 
	 * @param  bool $option
	 * @return Initializer
	 */
	public function setDirectoryForEachService($option = true) {
		$this->_dirForEachService = (bool) $option;
		
		return $this;
	}
	
	/**
	 * Register the services' namespace
	 * 
	 * @param  string $namespace
	 * @return Initializer
	 */
	public function registerNamespace($namespace) {
		if (! in_array($namespace, $this->_namespaces, true)) {
			$this->_namespaces[] = (string) $namespace;
		}
		
		return $this;
	}
	
	/**
	 * Initialize the service
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	public function initialize($id) {
		if (isset($this->_services[$id])) {
			return $this->_services[$id];
		}
		
		$service = $this->loadService($id);
		
		if ($this->_onInit !== null) {
			$this->_onInit->__invoke($service);
		}
		
		if ($service instanceof LocatorAwareInterface && $this->hasLocator() && ! $service->hasLocator()) {
			$service->setLocator($this->getLocator());
		}
		
		if ($service instanceof ConfigAwareInterface) {
			$configPart = strtolower($id);
			
			$service->setConfigServiceId($this->_configIdPrefix . $configPart . $this->_configIdPostfix);
			
			if ($this->hasConfig() && ! $service->hasConfig()) {
				$config = $this->getConfig();

				if (isset($config->$configPart)) {
					$service->setConfig($config->$configPart);
				}
			}
		}
		
		$this->_services[$id] = $service;
		
		return $service;
	}
	
	/**
	 * Is the service available
	 * 
	 * @param  string $id
	 * @return boolean
	 */
	public function isAvailable($id) {
		if (isset($this->_services[$id])) {
			return true;
		}
		
		if ($this->loadService($id, true)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Load the service
	 * 
	 * @param  string $id
	 * @return object | bool
	 * @throws NoNamespaces
	 * @throws NoHelper
	 */
	protected function loadService($id, $onlyCheck = false) {
		if (empty($this->_namespaces)) {
			throw new NoNamespaces('Wasn\'t a namespaces registered');
		}
		
		$misses = &$this->_loadMisses;
		set_error_handler(function() use(&$misses) {
			++ $misses;
		});
		
		foreach ($this->_namespaces as $namespace) {
			if ($this->_dirForEachService) {
				$namespace .= '\\' . $id;
			}

			$class = $namespace . '\\' . $this->_classPrefix . ucfirst($id) . $this->_classPostfix;
			
			if (class_exists($class)) {
				restore_error_handler();
				unset($misses);
				
				if ($onlyCheck) {
					return true;
				} else {
					return new $class();
				}
			}
		}
		
		restore_error_handler();
		unset($misses);
		
		if ($onlyCheck) {
			return false;
		} else {
			throw new NoService('Unable to load the service "' . $id . '", registered namespaces: "' . implode('", "', $this->_namespaces) . '"');
		}
	}
	
	/**
	 * Get a misses number which were occurred while a class loading
	 * 
	 * @return int
	 */
	public function getMisses() {
		return $this->_loadMisses;
	}
	
	/**
	 * Get a service
	 * 
	 * @param  string $id            An id of a service
	 * @param  int    $failBehaviour On a service locate fail behaviour
	 * @return mixed
	 */
	public function get($id, $failBehaviour = self::BEHAVIOUR_FAIL_EXCEPTION) {
		if ($failBehaviour === self::BEHAVIOUR_FAIL_NULL) {
			try {
				return $this->initialize($id);
			} catch (NoService $e) {
				return;
			}
		} else {
			return $this->initialize($id);
		}
	}
	
	/**
	 * Has a service
	 * 
	 * @param  string $name An id of a service
	 * @return boolean
	 */
	public function has($id) {
		return $this->isAvailable($id);
	}
	
	/**
	 * Check for a service has been initialized
	 * 
	 * @param  string $name An id of a service
	 * @return boolean
	 */
	public function hasInitialized($id) {
		return isset($this->_services[$id]);
	}

	public function __isset($name) {
		return $this->isAvailable($name);
	}
	
	public function __get($name) {
		return $this->initialize($name);
	}
	
}