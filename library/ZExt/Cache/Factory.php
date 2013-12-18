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
use ZExt\Cache\Backend\Decorators\Profileable;
use ZExt\Cache\Backend\Decorators\Taggable;
use ZExt\Cache\Backend\Decorators\SerializerJson;

use ZExt\Cache\Frontend\Wrapper;
use ZExt\Cache\Frontend\Factory as FrontendFactory;

use ZExt\Profiler\ProfileableInterface;

use Traversable;

/**
 * Cache system's factory
 * 
 * Creates a full stack of a backend(s), decorators, frontends by the passed params
 * Parameters can be an array or a traversable implementation
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Factory
 * @author     Mike.Mirten
 * @version    1.0.2
 */
class Factory {
	
	// Factory's params
	const PARAM_TYPE         = 'type';
	const PARAM_TAGS         = 'tags';
	const PARAM_TAGS_BACKEND = 'tags_backend';
	const PARAM_LIFETIME     = 'lifetime';
	const PARAM_SERIALIZE    = 'serialize';
	const PARAM_PROFILER     = 'profiler';
	
	const BACKENDS_NAMESPACE = 'ZExt\Cache\Backend';
	const DEFAULT_BACKEND    = 'Memcache';
	
	/**
	 * Create the backend
	 * 
	 * Parameters:
	 * param name   | datatype | description
	 * =====================================
	 * type         | string   | Type of the backend (memcache, files...), memcache will be used as default
	 * profiler     | bool     | Queries must be profileable (implements the "ZExt\Profiler\ProfileableInterface")
	 * tags         | bool     | Backend must support the operations with a tags (save with tags, get by tags...)
	 * tags_backend | array    | Tags must be stored in a separated backend with personal params (regardless of tags supporting by backend)
	 * serialize    | string   | Serialize a data (eg. into json)
	 * 
	 * Other params will be passed to the backend's constructor
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
		
		// Profiler
		if (isset($options[self::PARAM_PROFILER])) {
			$profiler = (bool) $options[self::PARAM_PROFILER];
			unset($options[self::PARAM_PROFILER]);
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
		
		// Wrap to a profiler
		if (isset($profiler) && $profiler === true) {
			if (! $backend instanceof ProfileableInterface) {
				$backend = new Profileable($backend);
			}
			
			$backend->setProfilerStatus(true);
		}
		
		// Wrap to a serializer
		if (isset($serialize)) {
			if (strtolower($serialize) === 'json') {
				$backend = new SerializerJson($backend);
			}
		}
		
		if (! $tagsForced || ($backend instanceof TaggableInterface && ! isset($tagsBackendOptions))) {
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
	 * The "lifetime" option is only supported, others will be passed further
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
	 * The "lifetime" option is only supported, others will be passed further
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