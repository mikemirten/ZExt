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

namespace ZExt\I18n\Cache;

/**
 * Locale strategy
 * A cache key per a locale
 * 
 * @category   ZExt
 * @package    I18n
 * @subpackage Cache
 * @author     Mike.Mirten
 * @version    1.0
 */
class StrategyLocaleDomain extends StrategyAbstract {
	
	/**
	 * Cache namespace
	 *
	 * @var string 
	 */
	protected $namespace = 'zext_translator';
	
	/**
	 * Get the catalog(s) from the cache by the locale
	 * 
	 * @param  array | string $locale
	 * @param  array | string $domain
	 * @return array | null
	 */
	public function getCatalogs($locale = null, $domain = null) {
		$cache   = $this->getCacheFrontend();
		$cacheId = $this->prepareCacheId($locale, $domain);
		
		// Many
		if (is_array($cacheId)) {
			$catalogsRaw = $cache->getMany($cacheId);
			
			if (empty($catalogsRaw)) {
				return;
			}
			
			$catalogs = [];
			
			foreach ($catalogsRaw as $catalogId => $catalog) {
				list($localePart, $domainPart) = explode('_', $catalogId);
				
				if (! isset($catalogs[$localePart])) {
					$catalogs[$localePart] = [];
				}
				
				$catalogs[$localePart][$domainPart] = $catalog;
			}
			
			return $catalogs;
		}
		
		// One
		$catalog = $cache->get($cacheId);
				
		if ($catalog === null) {
			return;
		}

		return [
			$locale => [
				$domain => $catalog
			]
		];
	}
	
	/**
	 * Prepare the cahce IDs list
	 * 
	 * @param  string | array $locale
	 * @param  string | array $domain
	 * @return string
	 */
	protected function prepareCacheId($locale, $domain) {
		if (is_array($locale)) {
			$list = [];
			
			// many locales to many domains
			if (is_array($domain)) {
				foreach ($locale as $part1) {
					foreach ($domain as $part2) {
						$list[] = $part1 . '_' . $part2;
					}
				}
				
				return $list;
			}
			
			// many locales to one domain
			foreach ($locale as $part) {
				$list[] = $part1 . '_' . $domain;
			}
			
			return $list;
		}
		
		// one locale to many domains
		if (is_array($domain)) {
			$list = [];
			
			foreach ($domain as $part) {
				$list[] = $locale . '_' . $part;
			}
			
			return $list;
		}
		
		// one locale to one domain
		return $locale . '_' . $domain;
	}
	
	/**
	 * Store the catalogs in the cache
	 * 
	 * @param  array $catalogs
	 * @return bool
	 */
	public function setCatalogs(array $catalogs) {
		$cache = $this->getCacheFrontend();
		$pairs = [];
		
		foreach ($catalogs as $locale => $localeData) {
			foreach ($localeData as $domain => $domainData) {
				$pairs[$locale . '_' . $domain] = $domainData;
			}
		}
		
		if (count($pairs) === 1) {
			reset($pairs);
			
			return $cache->set(key($pairs), current($pairs), $this->lifetime);
		}
		
		return $cache->setMany($pairs, $this->lifetime);
	}
	
	/**
	 * Remove the catalog(s) from the cache
	 * 
	 * @param  array | string $locale
	 * @param  array | string $domain
	 * @return bool
	 */
	public function removeCatalogs($locale = null, $domain = null) {
		$cache   = $this->getCacheFrontend();
		$cacheId = $this->prepareCacheId($locale, $domain);
		
		if (is_array($cacheId)) {
			return $cache->removeMany($cacheId);
		}
		
		return $cache->remove($cacheId);
	}
	
}