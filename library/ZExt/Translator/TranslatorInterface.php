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

namespace ZExt\Translator;

/**
 * Translator interface
 * 
 * @category   ZExt
 * @package    Translator
 * @subpackage Translator
 * @author     Mike.Mirten
 * @version    1.0
 */
interface TranslatorInterface {
	
	const NOTFOUND_RETURN_ID = 1;
	const NOTFOUND_NOTICE    = 2;
	const NOTFOUND_EXCEPTION = 4;
	
	/**
	 * Translate the ID or message
	 * 
	 * @param  string $id     Translation template ID
	 * @param  array  $params Translation template parameters
	 * @param  string $domain Domain of ID's
	 * @param  string $locale Specify the locale
	 * @return string
	 */
	public function translate($id, $params = null, $domain = null, $locale = null);
	
	/**
	 * Set the locale
	 * 
	 * @param  string $locale
	 * @return TranslatorInterface
	 */
	public function setLocale($locale);
	
	/**
	 * Get the locale
	 * 
	 * @return string
	 */
	public function getLocale();
	
	/**
	 * Set the behaviour of not found ID's handling
	 * 
	 * @param  int $behaviour Bitbucket, see NOTFOUND_* constants
	 * @return TranslatorInterface
	 */
	public function setFailBehaviour($behaviour);
	
	/**
	 * Set the behaviour of not found ID's handling
	 * 
	 * @return int
	 */
	public function getFailBehaviour();
	
}