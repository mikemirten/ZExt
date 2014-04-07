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

use ZExt\Translator\Exceptions\InvalidOptions;

/**
 * Plural chooser
 * 
 * @category   ZExt
 * @package    Translator
 * @subpackage Plural
 * @author     Mike.Mirten
 * @version    1.0
 */
class PluralChooser implements PluralChooserInterface {

	/**
	 * Locale to a rule's method map
	 *
	 * @var array
	 */
	static protected $rulesMap = [
		'bo'  => 'rule0', 'dz'  => 'rule0', 'id'  => 'rule0', 'ja'  => 'rule0',
		'jv'  => 'rule0', 'ka'  => 'rule0', 'km'  => 'rule0', 'kn'  => 'rule0',
		'ko'  => 'rule0', 'ms'  => 'rule0', 'th'  => 'rule0', 'tr'  => 'rule0',
		'vi'  => 'rule0', 'zh'  => 'rule0', 'af'  => 'rule1', 'az'  => 'rule1',
		'bn'  => 'rule1', 'bg'  => 'rule1', 'ca'  => 'rule1', 'da'  => 'rule1',
		'de'  => 'rule1', 'el'  => 'rule1', 'en'  => 'rule1', 'eo'  => 'rule1',
		'es'  => 'rule1', 'et'  => 'rule1', 'eu'  => 'rule1', 'fa'  => 'rule1',
		'fi'  => 'rule1', 'fo'  => 'rule1', 'fy'  => 'rule1', 'gl'  => 'rule1',
		'gu'  => 'rule1', 'ha'  => 'rule1', 'he'  => 'rule1', 'hu'  => 'rule1',
		'is'  => 'rule1', 'it'  => 'rule1', 'ku'  => 'rule1', 'lb'  => 'rule1',
		'ml'  => 'rule1', 'mn'  => 'rule1', 'mr'  => 'rule1', 'nb'  => 'rule1',
		'ne'  => 'rule1', 'nl'  => 'rule1', 'nn'  => 'rule1', 'no'  => 'rule1',
		'om'  => 'rule1', 'or'  => 'rule1', 'pa'  => 'rule1', 'ps'  => 'rule1',
		'pt'  => 'rule1', 'so'  => 'rule1', 'sq'  => 'rule1', 'sv'  => 'rule1',
		'sw'  => 'rule1', 'ta'  => 'rule1', 'te'  => 'rule1', 'tk'  => 'rule1',
		'ur'  => 'rule1', 'zu'  => 'rule1', 'pap' => 'rule1', 'nah' => 'rule1',
		'fur' => 'rule1', 'am'  => 'rule2', 'bh'  => 'rule2', 'fr'  => 'rule2',
		'hi'  => 'rule2', 'ln'  => 'rule2', 'mg'  => 'rule2', 'ti'  => 'rule2',
		'wa'  => 'rule2', 'fil' => 'rule2', 'xbr' => 'rule2', 'nso' => 'rule2',
		'gun' => 'rule2', 'be'  => 'rule3', 'bs'  => 'rule3', 'hr'  => 'rule3',
		'ru'  => 'rule3', 'sr'  => 'rule3', 'uk'  => 'rule3', 'cs'  => 'rule4',
		'sk'  => 'rule4', 'ga'  => 'rule5', 'lt'  => 'rule6', 'sl'  => 'rule7',
		'mk'  => 'rule8', 'mt'  => 'rule9', 'lv'  => 'ruleA', 'pl'  => 'ruleB',
		'cy'  => 'ruleC', 'ro'  => 'ruleD', 'ar'  => 'ruleE'
	];
	
	/**
	 * Default locale
	 *
	 * @var string
	 */
	protected $locale;
	
	/**
	 * Constructor
	 * 
	 * @param string $locale
	 */
	public function __construct($locale = null) {
		if ($locale !== null) {
			$this->setLocale($locale);
		}
	}

	/**
	 * Choose the plural variant
	 * 
	 * @param  int   $number
	 * @param  array 
	 * @return string
	 */
	public function choose($number, array $variants, $locale = null) {
		if ($locale === null) {
			$locale = $this->getLocale();
		} else {
			$locale = $this->normalizeLocale($locale);
		}
		
		if (! isset(self::$rulesMap[$locale])) {
			$position = 0;
		} else {
			$rule     = self::$rulesMap[$locale];
			$position = $this->$rule((int) $number);
		}
		
		if (! isset($variants[$position])) {
			throw new InvalidOptions('There is no variant by position: "' . $position . '"');
		}
		
		return $variants[$position];
	}
	
	/**
	 * Set the locale
	 * 
	 * @param  string $locale
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
	 * Normalize the locale
	 * 
	 * @param  string $locale
	 * @return string
	 */
	protected function normalizeLocale($locale) {
		$underlinePos = strpos($locale, '_');
		
		if ($underlinePos !== false) {
			return substr($locale, 0, $underlinePos);
		}
		
		return $locale;
	}
	
	// bo, dz, id, ja, jv, ka, km, kn, ko, ms, th, tr, vi, zh
	protected function rule0() {
		return 0;
	}
	
	// af, az, bn, bg, ca, da, de, el, en, eo, es, et, eu, fa, fi, fo, fy, gl,
	// gu, ha, he, hu, is, it, ku, lb, ml, mn, mr, nb, ne, nl, nn, no, om, or,
	// pa, ps, pt, so, sq, sv, sw, ta, te, tk, ur, zu, pap, nah, fur
	protected function rule1($number) {
		return ($number === 1) ? 0 : 1;
	}
	
	// am, bh, fr, hi, ln, mg, ti, wa, fil, xbr, nso, gun
	protected function rule2($number) {
		return ($number === 0 || $number === 1) ? 0 : 1;
	}
	
	// be, bs, hr, ru, sr, uk
	protected function rule3($number) {
		if ($number % 10 === 1 && $number % 100 !== 11) {
			return 0;
		}
		
		if ($number % 10 >= 2  && $number % 10 <= 4
		&& ($number % 100 < 10 || $number % 100 >= 20)) {
			return 1;
		}
		
		return 2;
	}
	
	// cs, sk
	protected function rule4($number) {
		if ($number === 1) {
			return 0;
		}
		
		return ($number >= 2 && $number <= 4) ? 1 : 2;
	}
	
	// ga
	protected function rule5($number) {
		if ($number === 1) {
			return 0;
		}
		
		return ($number === 2) ? 1 : 2;
	}
	
	// lt
	protected function rule6($number) {
		if ($number % 10 === 1 && $number % 100 !== 11) {
			return 0;
		}
		
		if ($number % 10 >= 2 && ($number % 100 < 10 || $number % 100 >= 20)) {
			return 1;
		}
		
		return 2;
	}
	
	// sl
	protected function rule7($number) {
		if ($number % 100 === 1) {
			return 0;
		}
		
		if ($number % 100 === 2) {
			return 1;
		}
		
		return ($number % 100 === 3 || $number % 100 === 4) ? 2 : 3;
	}
	
	// mk
	protected function rule8($number) {
		return ($number % 10 === 1) ? 0 : 1;
	}
	
	// mt
	protected function rule9($number) {
		if ($number === 1) {
			return 0;
		}
		
		if ($number === 0 || ($number % 100 > 1 && $number % 100 < 11)) {
			return 1;
		}
		
		return ($number % 100 > 10 && $number % 100 < 20) ? 2 : 3;
	}
	
	// lv
	protected function ruleA($number) {
		if ($number === 0) {
			return 0;
		}
		
		return ($number % 10 === 1 && $number % 100 !== 11) ? 1 : 2;
	}
	
	// pl
	protected function ruleB($number) {
		if ($number === 1) {
			return 0;
		}
		
		if ($number % 10 >= 2  && $number % 10 <= 4
		&& ($number % 100 < 12 || $number % 100 > 14)) {
			return 1;
		}
		
		return 2;
	}
	
	// cy
	protected function ruleC($number) {
		if ($number === 1) {
			return 0;
		}
		
		if ($number === 2) {
			return 1;
		}
		
		return ($number === 8 || $number === 11) ? 2 : 3;
	}
	
	// ro
	protected function ruleD($number) {
		if ($number === 1) {
			return 0;
		}
		
		if ($number === 0 || ($number % 100 > 0 && $number % 100 < 20)) {
			return 1;
		}
		
		return 2;
	}
	
	// ar
	protected function ruleE($number) {
		if ($number >= 0 && $number < 3) {
			return $number;
		}
		
		if (($number >= 3) && ($number <= 10)) {
			return 3;
		}
		
		return ($number >= 11 && $number <= 99) ? 4 : 5;
	}

}