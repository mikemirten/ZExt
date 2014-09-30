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
class StrategyLocale extends StrategyAbstract {
	
	/**
	 * Cache namespace
	 *
	 * @var string 
	 */
	protected $namespace = 'zext_translator_locale';
	
	/**
	 * Get the catalog(s) from the cache by the locale
	 * 
	 * @param  array | string $locale
	 * @param  array | string $domain
	 * @return array | null
	 */
	public function getCatalogs($locale = null, $domain = null) {
		$cache = $this->getCacheFrontend();
		
		if (is_array($locale)) {
			$catalogs = $cache->getMany($locale);
			
			return empty($catalogs) ? null : $catalogs;
		}
		
		$catalogs = $cache->get($locale);
		
		if ($catalogs === null) {
			return;
		}
		
		return [$locale => $catalogs];
	}
	
	/**
	 * Store the catalogs in the cache
	 * 
	 * @param  array $catalogs
	 * @return bool
	 */
	public function setCatalogs(array $catalogs) {
		$cache = $this->getCacheFrontend();
		
		if (count($catalogs) === 1) {
			reset($catalogs);
			
			return $cache->set(key($catalogs), current($catalogs), $this->lifetime);
		}
		
		return $cache->setMany($catalogs, $this->lifetime);
	}
	
	/**
	 * Remove the catalog(s) from the cache
	 * 
	 * @param  array | string $locale
	 * @param  array | string $domain
	 * @return bool
	 */
	public function removeCatalogs($locale = null, $domain = null) {
		$cache = $this->getCacheFrontend();
		
		if (is_array($locale)) {
			return $cache->removeMany($locale);
		}
		
		return $cache->remove($locale);
	}
	
}