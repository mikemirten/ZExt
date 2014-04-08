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

namespace ZExt\I18n;

use ZExt\I18n\Resource\ResourceInterface;
use ZExt\I18n\Cache\StrategyInterface;
use ZExt\I18n\Plugin\PluginInterface;

use ZExt\I18n\Exceptions\NoLocale;
use ZExt\I18n\Exceptions\NoResources;
use ZExt\I18n\Exceptions\NoTranslation;

use ZExt\Components\OptionsTrait;
use Traversable;

/**
 * Translator
 * 
 * @category   ZExt
 * @package    I18n
 * @subpackage Translator
 * @author     Mike.Mirten
 * @version    1.0beta
 */
class Translator implements TranslatorInterface {
	
	use OptionsTrait;
	
	/**
	 * Default locale
	 *
	 * @var string
	 */
	protected $locale;
	
	/**
	 * Fallback locales
	 *
	 * @var array
	 */
	protected $fallbackLocales = [];
	
	/**
	 * Default domain
	 *
	 * @var string
	 */
	protected $domain = 'default';
	
	/**
	 * Provided resources
	 *
	 * @var ResourceInterface[]
	 */
	protected $resources = [];
	
	/**
	 * Translation's catalogs
	 * [locale][domain][id]
	 *
	 * @var array
	 */
	protected $catalogs = [];
	
	/**
	 * Initialized catalogs' list
	 *
	 * @var array
	 */
	protected $initializedCatalogs = [];
	
	/**
	 * Not found ID behaviour
	 *
	 * @var int
	 */
	protected $failBehaviour = self::NOTFOUND_RETURN_ID;
	
	/**
	 * Caching strategy
	 *
	 * @var StrategyInterface
	 */
	protected $cache;
	
	/**
	 * Locales which must be preinitialized
	 *
	 * @var array
	 */
	protected $preinitLocales = [];
		
	/**
	 * Domains which must be preinitialized
	 *
	 * @var array
	 */
	protected $preinitDomains = [];
	
	/**
	 * Preinitialization enabled
	 *
	 * @var bool
	 */
	protected $preinitNeed = false;
	
	/**
	 * Use the char as a domain delimiter
	 *
	 * @var string
	 */
	protected $idDelimiter = '.';
	
	/**
	 * Translation processing plugins
	 *
	 * @var PluginInterface[] 
	 */
	protected $plugins = [];
	
	/**
	 * Parameters' definition pattern
	 *
	 * @var string
	 */
	protected $paramsPattern = '{{%s}}';
	
	/**
	 * Constructor
	 * 
	 * @param Locale | string   $locale   Default locale | Options as an array or a traversable implementation
	 * @param ResourceInterface $resource A resource instance or an array of instances
	 * @param LocatorInterface  $locator  Cache strategy instance
	 */
	public function __construct($locale = null, $resource = null, StrategyInterface $cache = null) {
		if ($locale !== null) {
			if (is_array($locale) || $locale instanceof Traversable) {
				$this->setOptions($locale);
			} else {
				$this->setLocale($locale);
			}
		}
		
		if ($resource !== null) {
			if (is_array($resource)) {
				$this->addResources($resource);
			} else {
				$this->addResource($resource);
			}
		}
		
		if ($cache !== null) {
			$this->setCache($cache);
		}
	}
	
	/**
	 * Set the cache strategy
	 * 
	 * @param  StrategyInterface $strategy
	 * @return Translator
	 */
	public function setCache(StrategyInterface $strategy) {
		$this->cache = $strategy;
		
		return $this;
	}
	
	/**
	 * Get the caching strategy
	 * 
	 * @return StrategyInterface
	 */
	public function getCache() {
		return $this->cache;
	}
	
	/**
	 * Translate the ID or message
	 * 
	 * @param  string $id     Translation template ID
	 * @param  array  $params Translation template parameters
	 * @param  string $domain Domain of ID's
	 * @param  string $locale Specify the locale
	 * @return string
	 */
	public function translate($id, $params = null, $domain = null, $locale = null) {
		if ($domain === null) {
			$domain = $this->handleDomain($id);
		}
		
		if ($locale === null) {
			$locale = $this->getLocale();
		} else {
			$locale = $this->normalizeLocale($locale);
		}
		
		$translation = $this->getTranslation($id, $domain, $locale, $this->fallbackLocales);
		
		if ($translation === null) {
			return $this->handleFailure($id);
		}
		
		if (! empty($this->plugins)) {
			$translation = $this->handlePlugins($locale, $translation, $params);
		}
		
		if ($params === null) {
			return $translation;
		}
		
		return strtr($translation, $this->normalizeParams($params));
	}
	
	/**
	 * Handle the translation's domain
	 * 
	 * @param  string $id
	 * @return string
	 */
	protected function handleDomain(&$id) {
		if ($this->idDelimiter === null) {
			return $this->domain;
		}
		
		$delimiterPos = strpos($id, $this->idDelimiter);
		
		if ($delimiterPos === false) {
			return $this->domain;
		}
		
		$domain = substr($id, 0, $delimiterPos);
		$id     = substr($id, $delimiterPos + 1);
		
		return $domain;
	}
	
	/**
	 * Normalize the parameters by pattern
	 * 
	 * @param  array $params
	 * @return array
	 */
	protected function normalizeParams(array $params) {
		$processed = [];
		
		foreach ($params as $param => $value) {
			$processed[sprintf($this->paramsPattern, $param)] = $value;
		}
		
		return $processed;
	}
	
	/**
	 * Get the translation
	 * 
	 * @param  string $id
	 * @param  string $domain
	 * @param  string $locale
	 * @param  array  $fallbacks
	 * @return string
	 */
	protected function getTranslation($id, $domain, $locale, $fallbacks) {
		// Is initialize of the catalog need ?
		if (! isset($this->initializedCatalogs[$locale][$domain])) {
			$this->initCatalogs($locale, $domain);
		}
		
		// Is failure occurred ?
		if (! isset($this->catalogs[$locale][$domain][$id])) {
			if (empty($fallbacks)) {
				return;
			}
			
			$fallbackLocale = array_shift($fallbacks);
			
			return $this->getTranslation($id, $domain, $fallbackLocale, $fallbacks);
		}
		
		return $this->catalogs[$locale][$domain][$id];
	}
	
	/**
	 * Handle the not found ID
	 * 
	 * @param  string $id
	 * @return string
	 * @throws NoTranslation
	 */
	protected function handleFailure($id) {
		if ($this->failBehaviour & self::NOTFOUND_NOTICE) {
			trigger_error('Translation absent for the ID "' . $id . '" in the catalogs');
		}
		
		if ($this->failBehaviour & self::NOTFOUND_RETURN_ID) {
			return $id;
		}
		
		if ($this->failBehaviour & self::NOTFOUND_EXCEPTION) {
			throw new NoTranslation('Translation absent for the ID "' . $id . '" in the catalogs');
		}
	}
	
	/**
	 * Set the parameters' definition pattern
	 * 
	 * @param  string $pattern
	 * @return Translator
	 */
	public function setParamsPattern($pattern) {
		$this->paramsPattern = (string) $pattern;
		
		return $this;
	}
	
	/**
	 * Get the parameters' definition pattern
	 * 
	 * @param  string $pattern
	 * @return Translator
	 */
	public function getParamsPattern() {
		return $this->paramsPattern;
	}
	
	/**
	 * Handle the plugins
	 * 
	 * @param  string       $translation
	 * @param  array | null $params
	 * @return string
	 */
	protected function handlePlugins($locale, $translation, $params) {
		foreach ($this->plugins as $plugin) {
			$translation = $plugin->handle($locale, $translation, $params);
		}
		
		return $translation;
	}
	
	/**
	 * Set the plugins (overrides the current)
	 * 
	 * @param  array $plugins
	 * @return Translator
	 */
	public function setPlugins(array $plugins) {
		$this->resetPlugins();
		$this->addPlugins($plugins);
		
		return $this;
	}
	
	/**
	 * Add the plugins
	 * 
	 * @param  array $plugins
	 * @return Translator
	 */
	public function addPlugins(array $plugins) {
		foreach ($plugins as $name => $plugin) {
			if (is_numeric($name)) {
				$this->addPlugin($plugin);
				continue;
			}
			
			$this->addPlugin($plugin, $name);
		}
		
		return $this;
	}
	
	/**
	 * Add the plugin
	 * 
	 * @param  PluginInterface $plugin
	 * @param  string $name
	 * @return Translator
	 */
	public function addPlugin(PluginInterface $plugin, $name = null) {
		if ($name === null) {
			$this->plugins[] = $plugin;
			
			return $this;
		}
		
		$this->plugins[$name] = $plugin;
		
		return $this;
	}
	
	/**
	 * Get the plugin
	 * 
	 * @param  string $name
	 * @return PluginInterface | null
	 */
	public function getPlugin($name) {
		if (isset($this->plugins[$name])) {
			return $this->plugins[$name];
		}
	}
	
	/**
	 * Get all the plugins
	 * 
	 * @return PluginInterface[]
	 */
	public function getPlugins() {
		return $this->plugins;
	}
	
	/**
	 * Has the plugin in the translator
	 * 
	 * @return bool
	 */
	public function hasPlugin($name) {
		return isset($this->plugins[$name]);
	}
	
	/**
	 * Remove the plugin
	 * 
	 * @param  string $name
	 * @return Translator
	 */
	public function removePlugin($name) {
		unset($this->plugins[$name]);
		
		return $this;
	}
	
	/**
	 * Remove all the plugins
	 * 
	 * @return Translator
	 */
	public function resetPlugins() {
		$this->plugins = [];
		
		return $this;
	}

	/**
	 * Set the domain delimiter
	 * 
	 * @param  string $delimiter
	 * @return Translator
	 */
	public function setDomainDelimiter($delimiter = '.') {
		$this->idDelimiter = (string) $delimiter;
		
		return $this;
	}
	
	/**
	 * Get the domain delimiter
	 * 
	 * @return string | null
	 */
	public function getDomainDelimiter() {
		return $this->idDelimiter;
	}
	
	/**
	 * Set the locale
	 * 
	 * @param  Locale | string $locale
	 * @return Translator
	 */
	public function setLocale($locale) {
		$this->locale = $this->normalizeLocale($locale);
		
		return $this;
	}
	
	/**
	 * Get the locale
	 * 
	 * @return string
	 */
	public function getLocale() {
		if ($this->locale === null) {
			throw new NoLocale('Locale must be set first');
		}
		
		return $this->locale;
	}
	
	/**
	 * Set the fallback locales (overrides the exists)
	 * 
	 * @param  array $locales
	 * @param  bool  $preinit
	 * @return Translator
	 */
	public function setFallbackLocales(array $locales, $preinit = false) {
		$this->resetFallbackLocales();
		$this->addFallbackLocales($locales, $preinit);
		
		return $this;
	}
	
	/**
	 * Add the fallback locales
	 * 
	 * @param  array $locales
	 * @param  bool  $preinit
	 * @return Translator
	 */
	public function addFallbackLocales(array $locales, $preinit = false) {
		foreach ($locales as $locale) {
			$this->addFallbackLocale($locale, $preinit);
		}
		
		return $this;
	}
	
	/**
	 * Add the fallback locale
	 * 
	 * @param  string $locale
	 * @param  bool   $preinit
	 * @return Translator
	 */
	public function addFallbackLocale($locale, $preinit = false) {
		$locale = (string) $locale;
		$locale = $this->normalizeLocale($locale);
		
		if (in_array($locale, $this->fallbackLocales, true)) {
			return $this;
		}
		
		$this->fallbackLocales[] = $locale;
		
		if ($preinit) {
			$this->addPreinitLocale($locale);
		}
		
		return $this;
	}
	
	/**
	 * Get the fallback locales' list
	 * 
	 * @return array
	 */
	public function getFallbackLocales() {
		return $this->fallbackLocales;
	}
	
	/**
	 * Reset the fallback locales' list
	 * 
	 * @return Translator
	 */
	public function resetFallbackLocales() {
		$this->fallbackLocales = [];
		
		return $this;
	}
	
	/**
	 * Normalize the locale
	 * 
	 * @param  string $locale
	 * @return string
	 */
	protected function normalizeLocale($locale) {
		if ($locale instanceof Locale) {
			$locale = $locale->getPrimaryLanguage();
		}
		
		$underlinePos = strpos($locale, '_');
		
		if ($underlinePos !== false) {
			return substr($locale, 0, $underlinePos);
		}
		
		return $locale;
	}

	/**
	 * Set the default domain
	 * 
	 * @param  string $domain
	 * @return Translator
	 */
	public function setDomain($domain) {
		$this->domain = (string) $domain;
		
		return $this->domain;
	}
	
	/**
	 * Get the default domain
	 * 
	 * @return string
	 */
	public function getDomain() {
		return $this->domain;
	}
	
	/**
	 * Set the behaviour of not found ID's handling
	 * 
	 * @param  int $behaviour Bitbucket, see NOTFOUND_* constants
	 * @return Translator
	 */
	public function setFailBehaviour($behaviour) {
		$this->failBehaviour = (int) $behaviour;
		
		return $this;
	}
	
	/**
	 * Set the behaviour of not found ID's handling
	 * 
	 * @return int
	 */
	public function getFailBehaviour() {
		return $this->failBehaviour;
	}
	
	/**
	 * Set the resources (overrides the current)
	 * 
	 * @param  array $resources
	 * @return Translator
	 */
	public function setResources(array $resources) {
		$this->resetResources();
		$this->addResources($resources);
		
		return $this;
	}
	
	/**
	 * Add the resources
	 * 
	 * @param array $resources
	 */
	public function addResources(array $resources) {
		foreach ($resources as $name => $resource) {
			if (is_numeric($name)) {
				$this->addResource($resource);
				continue;
			}
			
			$this->addResource($resource, $name);
		}
		
		return $this;
	}
	
	/**
	 * Add the resource
	 * 
	 * @param  string            $name     Optional name of the resource, will override the exists one
	 * @param  ResourceInterface $resource
	 * @return Translator
	 */
	public function addResource(ResourceInterface $resource, $name = null) {
		if ($name === null) {
			$this->resources[] = $resource;
			
			return $this;
		}
		
		$this->resources[$name] = $resource;
		
		return $this;
	}
	
	/**
	 * Get the resource
	 * 
	 * @param  string $name
	 * @return ResourceInterface
	 */
	public function getResource($name) {
		if (isset($this->resources[$name])) {
			return $this->resources[$name];
		}
	}
	
	/**
	 * Get al the resources
	 * 
	 * @return ResourceInterface[]
	 */
	public function getResources() {
		return $this->resources;
	}
	
	/**
	 * Has the resource
	 * 
	 * @param string $name
	 */
	public function hasResource($name) {
		return isset($this->resources[$name]);
	}
	
	/**
	 * Remove the resource
	 * 
	 * @param  string $name
	 * @return Translator
	 */
	public function removeResource($name) {
		unset($this->resources[$name]);
		
		return $this;
	}
	
	/**
	 * Remove all resources
	 * 
	 * @return Translator
	 */
	public function resetResources() {
		$this->resources = [];
		
		return $this;
	}
	
	/**
	 * Reinitialize the catalogs
	 * 
	 * @return Translator
	 */
	public function reinitializeCatalogs() {
		$this->catalogs            = [];
		$this->initializedCatalogs = [];
		
		if (! empty($this->preinitLocales) || ! empty($this->preinitDomains)) {
			$this->preinitNeed = true;
		}
		
		return $this;
	}
	
	/**
	 * Add the locale to preinitialize
	 * 
	 * @param  string $locale
	 * @return Translator
	 */
	public function addPreinitLocale($locale) {
		$locale = (string) $locale;
		
		if (in_array($locale, $this->preinitLocales, true)) {
			return $this;
		}
		
		$this->preinitLocales[] = $locale;
		$this->preinitNeed      = true;
		
		return $this;
	}
	
	/**
	 * Add the domain to preinitialize
	 * 
	 * @param  string $domain
	 * @return Translator
	 */
	public function addPreinitDomain($domain) {
		$domain = (string) $domain;
		
		if (in_array($domain, $this->preinitDomains, true)) {
			return $this;
		}
		
		$this->preinitDomains[] = $domain;
		$this->preinitNeed      = true;
		
		return $this;
	}
	
	/**
	 * Initialize the catalogs for the locale(s) and domain(s)
	 * 
	 * @param  string | array $locale
	 * @param  string | array $domain
	 * @throws NoResources
	 */
	protected function initCatalogs($locale, $domain) {
		if (empty($this->resources)) {
			throw new NoResources('Resources hasn\'t been provided');
		}
		
		// Add the preinit data to the request if need
		if ($this->preinitNeed) {
			list($locale, $domain) = $this->handlePreinit($locale, $domain);
		}
		
		// Mark as initialized anyway
		$this->markAsInitialized($locale, $domain);
		
		// Try from cache
		if ($this->cache !== null) {
			$catalogsCached = $this->cache->getCatalogs($locale, $domain);
			
			if ($catalogsCached !== null) {
				$this->catalogs = array_replace_recursive($this->catalogs, $catalogsCached);
				
				// Request to the resources is still need ?
				$requestDiff = $this->calculateRequestDiff($locale, $domain, $catalogsCached);
				
				if ($requestDiff === null) {
					return;
				}
				
				list($locale, $domain) = $requestDiff;
			}
		}
		
		// Try from resources
		$catalogs = $this->getCatalogsFromResources($locale, $domain);
		
		if (! empty($catalogs)) {
			$this->catalogs = array_replace_recursive($this->catalogs, $catalogs);
			
			// Store in the cache just obtained data
			if ($this->cache !== null) {
				$this->cache->setCatalogs($catalogs);
			}
		}
	}
	
	/**
	 * Handle the preinitialize routine
	 * 
	 * @param  array | string $locale
	 * @param  array | string $domain
	 * @return array [locale, domain]
	 */
	protected function handlePreinit($locale, $domain) {
		if (! empty($this->preinitLocales)) {
			$locale = array_unique(array_merge(
				(array) $locale, 
				$this->preinitLocales
			));
		}

		if (! empty($this->preinitDomains)) {
			$domain = array_unique(array_merge(
				(array) $domain,
				$this->preinitDomains
			));
		}
		
		$this->preinitNeed = false;
		
		return [$locale, $domain];
	}
	
	/**
	 * Calculate difference between the requested data and the obtained
	 * 
	 * @param  array | string $locale
	 * @param  array | string $domain
	 * @param  array          $catalogs
	 * @return array | null   [locale, domain] Need to request | Need no request
	 */
	protected function calculateRequestDiff($locale, $domain, array $catalogs) {
		$domain = array_flip((array) $domain);
		$locale = array_flip((array) $locale);
		
		$diffLocale = array_diff_key($locale, $catalogs);
		$diffDomain = empty($diffLocale) ? [] : $domain;
		
		foreach ($catalogs as $localeId => $catalog) {
			$diff = array_diff_key($domain, $catalog);
			
			if (! empty($diff)) {
				$diffDomain           += $diff;
				$diffLocale[$localeId] = true;
			}
		}
		
		if (empty($diffLocale)) {
			return;
		}
		
		return [
			$this->normalizeRequestDiff($diffLocale),
			$this->normalizeRequestDiff($diffDomain)
		];
	}
	
	/**
	 * Normalize the difference data
	 * 
	 * @param  array $diff
	 * @return array | string
	 */
	protected function normalizeRequestDiff($diff) {
		if (count($diff) === 1) {
			reset($diff);
			return key($diff);
		}
		
		return array_keys($diff);
	}
	
	/**
	 * Initialize catalogs from the resources
	 * 
	 * @param  string | array $locale
	 * @param  string | array $domain
	 * @return array
	 */
	protected function getCatalogsFromResources($locale, $domain) {
		$catalogs = [];
		
		foreach ($this->resources as $resource) {
			$catalogs = array_replace_recursive($catalogs, $resource->getCatalogs($locale, $domain));
		}
		
		return $catalogs;
	}
	
	/**
	 * Mark the catalogs as initialized
	 * 
	 * @param string | array $locale
	 * @param string | array $domain
	 */
	protected function markAsInitialized($locale, $domain) {
		if (is_array($locale)) {
			foreach ($locale as $part) {
				$this->markAsInitializedLocale($part, $domain);
			}
			
			return;
		}
		
		$this->markAsInitializedLocale($locale, $domain);
	}
	
	/**
	 * Mark the catalogs as initialized (with exact locale)
	 * 
	 * @param string         $locale
	 * @param string | array $domain
	 */
	protected function markAsInitializedLocale($locale, $domain) {
		if (! isset($this->initializedCatalogs[$locale])) {
			$this->initializedCatalogs[$locale] = [];
		}

		if (is_string($domain)) {
			$this->initializedCatalogs[$locale][$domain] = true;
			return;
		}

		if (is_array($domain)) {
			$this->initializedCatalogs[$locale] += array_combine($domain, array_fill_keys($domain, true));
			return;
		}
	}
	
	/**
	 * Remove the catalogs from cache by domain(s) and/or locale(s)
	 * 
	 * @param  string | array $domain
	 * @param  string | array $locale
	 * @return bool
	 */
	public function removeFromCache($domain = null, $locale = null) {
		if ($this->cache === null) {
			return true;
		}
		
		if ($domain === null) {
			$domain = $this->domain;
		}
		
		if ($locale === null) {
			$locale = $this->getLocale();
		}
		
		return $this->cache->removeCatalogs($locale, $domain);
	}
	
}