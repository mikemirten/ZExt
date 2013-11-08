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

namespace ZExt\Cache;

use ZExt\Cache\Exceptions\InvalidOptions;
use ZExt\Cache\Backend\TaggableInterface;
use ZExt\Cache\Backend\Decorators\Taggable;
use ZExt\Cache\Backend\Decorators\SerializerJson;
use ZExt\Cache\Frontend\Wrapper;
use ZExt\Cache\Frontend\Factory as FrontendFactory;

use Traversable;

/**
 * Cache system's factory
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Factory
 * @author     Mike.Mirten
 * @version    1.0
 */
class Factory {
	
	const PARAM_TYPE         = 'type';
	const PARAM_TAGS         = 'tags';
	const PARAM_TAGS_BACKEND = 'tags_backend';
	const PARAM_LIFETIME     = 'lifetime';
	const PARAM_SERIALIZE    = 'serialize';
	
	const BACKENDS_NAMESPACE = 'ZExt\Cache\Backend';
	const DEFAULT_BACKEND    = 'Memcache';
	
	/**
	 * Create the backend
	 * 
	 * @param  array | Traversable $options
	 * @return \ZExt\Cache\Backend\BackendInterface
	 */
	static function createBackend($options = []) {
		$options = self::normalizeOptions($options);
		
		// Backend type
		if (isset($options[self::PARAM_TYPE])) {
			$backendType = (string) $options[self::PARAM_TYPE];
			unset($options[self::PARAM_TYPE]);
		} else {
			$backendType = self::DEFAULT_BACKEND;
		}
		
		// Tags ability
		if (isset($options[self::PARAM_TAGS])) {
			$tagsForced = (bool) $options[self::PARAM_TAGS];
			unset($options[self::PARAM_TAGS]);
		} else {
			$tagsForced = false;
		}
		
		// Tags backend
		if (isset($options[self::PARAM_TAGS_BACKEND])) {
			$tagsBackendOptions = $options[self::PARAM_TAGS_BACKEND];
			unset($options[self::PARAM_TAGS_BACKEND]);
		}
		
		// Serialize
		if (isset($options[self::PARAM_SERIALIZE])) {
			$serialize = $options[self::PARAM_SERIALIZE];
			unset($options[self::PARAM_SERIALIZE]);
		}
		
		$backendClass = self::BACKENDS_NAMESPACE . '\\' . ucfirst($backendType);
		$backend      = new $backendClass($options);
		
		// Wrap to a serializer
		if (isset($serialize)) {
			if (strtolower($serialize) === 'json') {
				$backend = new SerializerJson($backend);
			}
		}
		
		if (! $tagsForced || $backend instanceof TaggableInterface) {
			return $backend;
		}
		
		$tagsDecorator = new Taggable($backend);
		
		if (isset($tagsBackendOptions)) {
			$tagsDecorator->setTagHolderBackend(self::createBackend($tagsBackendOptions));
		}
		
		return $tagsDecorator;
	}
	
	/**
	 * Create the frontend
	 * 
	 * @param  array | Traversable $options
	 * @return Wrapper
	 */
	static public function createFrontend($options = []) {
		$options = self::normalizeOptions($options);
		
		if (isset($options[self::PARAM_LIFETIME])) {
			$lifetime = (int) $options[self::PARAM_LIFETIME];
			unset($options[self::PARAM_LIFETIME]);
		}
		
		$frontend = new Wrapper(self::createBackend($options));
		
		if (isset($lifetime)) {
			$frontend->setDefaultLifetime($lifetime);
		}
		
		return $frontend;
	}
	
	/**
	 * Create the frontend factory
	 * 
	 * @param  array | Traversable $options
	 * @return FrontendFactory
	 */
	static public function createFrontendFactory($options = []) {
		$options = self::normalizeOptions($options);
		
		if (isset($options[self::PARAM_LIFETIME])) {
			$lifetime = (int) $options[self::PARAM_LIFETIME];
			unset($options[self::PARAM_LIFETIME]);
		}
		
		$frontend = new FrontendFactory(self::createBackend($options));
		
		if (isset($lifetime)) {
			$frontend->setDefaultLifetime($lifetime);
		}
		
		return $frontend;
	}
	
	/**
	 * Normalize the options
	 * 
	 * @param  array | Traversable $options
	 * @return array
	 * @throws InvalidOptions
	 */
	static protected function normalizeOptions($options) {
		if ($options instanceof Traversable) {
			$options = iterator_to_array($options);
		}
		
		if (! is_array($options)) {
			throw new InvalidOptions('Options must be an array or a Traversable implementation');
		}
		
		return $options;
	}
	
}