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

namespace ZExt\I18n\Resource;

/**
 * Translator native array resource
 * 
 * @category   ZExt
 * @package    I18n
 * @subpackage Resource
 * @author     Mike.Mirten
 * @version    1.0
 */
class NativeArray extends ResourceAbstract {
	
	/**
	 * Constructor
	 * 
	 * @param array $catalogs catalogs with locales and domains: [locale][domain][id]
	 */
	public function __construct(array $catalogs = null) {
		if ($catalogs !== null) {
			$this->setCatalogsRaw($catalogs);
		}
	}
	
	/**
	 * Add the catalog for the locale
	 * Must contains an array with domain(s)
	 * 
	 * @param  string $locale
	 * @param  array  $catalog
	 * @return NativeArray
	 */
	public function addLocaleCatalog($locale, array $catalog) {
		$this->catalogs[$locale] = array_replace_recursive(
			$this->catalogs[$locale],
			$catalog
		);

		return $this;
	}
	
	/**
	 * Set the catalogs for all the locales and domains (overrides the current catalogs)
	 * Must contains an array with locales, and locales must contains an arrays with domain(s)
	 * 
	 * Raw source format:
	 * [
	 *     'locale' => [
	 *         'domain' => [
	 *             'id' => 'translation'
	 *         ]
	 *     ]
	 * ]
	 * 
	 * @param  array $catalogs
	 * @return NativeArray
	 */
	public function setCatalogsRaw(array $catalogs) {
		$this->catalogs = $catalogs;
		
		return $this;
	}
	
}