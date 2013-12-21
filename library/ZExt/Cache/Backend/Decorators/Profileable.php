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

namespace ZExt\Cache\Backend\Decorators;

use ZExt\Profiler\ProfilerExtendedInterface,
    ZExt\Profiler\ProfileableInterface,
    ZExt\Profiler\ProfileableTrait;

/**
 * Profiling ability decorator
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Decorators
 * @author     Mike.Mirten
 * @version    1.0
 */
class Profileable extends DecoratorAbstract implements ProfileableInterface {
	
	use ProfileableTrait;
	
	/**
	 * Fetch the data from the cache
	 * 
	 * @param  string $id
	 * @return mixed
	 */
	public function get($id) {
		$event  = $this->getProfiler()->startRead('Get: ' . $id);
		$result = $this->getBackend()->get($id);
		
		if ($result === null) {
			$event->stopNotice();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Fetch the many of the data from the cache
	 * 
	 * @param  array $id
	 * @return array
	 */
	public function getMany(array $ids) {
		$event  = $this->getProfiler()->startRead('Get (' . count($ids) . '): ' . implode(', ', $ids));
		$result = $this->getBackend()->getMany($ids);
		
		if (empty($result)) {
			$event->stopNotice();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Store the data into the cache
	 * 
	 * @param  string $id       ID of the stored data
	 * @param  mixed  $data     Stored data
	 * @param  int    $lifetime Lifetime in seconds
	 * @return bool
	 */
	public function set($id, $data, $lifetime = 0) {
		$event  = $this->getProfiler()->startWrite('Set: ' . $id);
		$result = $this->getBackend()->set($id, $data, $lifetime);
		
		if ($result === false) {
			$event->stopError();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Store the many of the date into the cache
	 * 
	 * @param  array $data
	 * @param  int   $lifetime
	 * @return bool
	 */
	public function setMany(array $data, $lifetime = 0) {
		$ids    = array_keys($data);
		$event  = $this->getProfiler()->startWrite('Set (' . count($ids) . '): ' . implode(', ', $ids));
		$result = $this->getBackend()->setMany($data, $lifetime);
		
		if ($result === false) {
			$event->stopError();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Check whether the data exists in the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function has($id) {
		$event  = $this->getProfiler()->startRead('Has: ' . $id);
		$result = $this->getBackend()->has($id);
		
		if ($result === false) {
			$event->stopNotice();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Remove the data from the cache
	 * 
	 * @param  string $id
	 * @return bool
	 */
	public function remove($id) {
		$event  = $this->getProfiler()->startDelete('Remove: ' . $id);
		$result = $this->getBackend()->remove($id);
		
		if ($result === false) {
			$event->stopError();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Remove the many the data from the cache
	 * 
	 * @param  array $id
	 * @return bool
	 */
	public function removeMany(array $ids) {
		$event  = $this->getProfiler()->startRead('Remove (' . count($ids) . '): ' . implode(', ', $ids));
		$result = $this->getBackend()->removeMany($ids);
		
		if ($result === false) {
			$event->stopError();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Increment the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 */
	public function inc($id, $value = 1) {
		$event  = $this->getProfiler()->startWrite('Inc: ' . $id);
		$result = $this->getBackend()->inc($id, $value);
		
		if ($result === false) {
			$event->stopError();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * Decrement the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 */
	public function dec($id, $value = 1) {
		$event  = $this->getProfiler()->startWrite('Dec: ' . $id);
		$result = $this->getBackend()->dec($id, $value);
		
		if ($result === false) {
			$event->stopError();
		} else {
			$event->stopSuccess();
		}
		
		return $result;
	}
	
	/**
	 * On profiler init
	 * 
	 * @param \ZExt\Profiler\ProfilerInterface $profiler
	 */
	protected function onProfilerInit($profiler) {
		if ($profiler instanceof ProfilerExtendedInterface) {
			$name = get_class($this->getBackend());
			$pos  = strrpos($name, '\\');
			
			$profiler->setName($pos === false ? $name : substr($name, $pos + 1));
			$profiler->setIcon('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAEeUlEQVRIS7VVXUwcVRT+7uwvSwe7sAUEky4l4AqI4ko0pURqaB8INo2xjQ0NUmPCm2jsg+XBxJ80pDXRB01M1JhW8EEeMLYvNukf9glKJbWQNlAbWZafdcHpws7s7O7Mes+dnRV91HCHm7M7d/i+c77znVmGbV5sm/FhE7BDZw6N1AXqKv4vIWNZXL1344upoalRwhIEclgO9Jx4OZJdNL3zNyNgTILDIeXZ8znwwPglSXTmgMTPKTodTjCJH+aApJ5E19EnMHZreuTy2cu9HNoU/131UlWos7NzNn4zzvQlHYGyUlRVVSKV0uEr8iIWX4Pb6cT9B78LULfbDY/XA6/HC6/XC7fLzfFzUB4q2P3CbtxdufvzxQ8vdnLotCAIHg927Htm31VOgGwsi9bw05i+PSOi8jABDweUeJZ0jwhcLhfcHiLgmxNQFZKDQdU0+Op9iJmx+dHB0ac4tCoIQv2hY821zd+tT6zDueksVLCyEsP6nwp2BcpQXOzDQiRqycKroSrcHl6J2yO+0/1sNgvNr8EIGIlzb52r4dDrgqBloOWd6vLqj9UJFT7DJx4WOnO9GRPii35IknXfJqBI1dBztHI5LpNLgVwvZ84PnW9M/JaYEwStJ1vPlj1SdhJTAFe1QGATbSX8+zORWQmIJHiTKfxhxFH9bHVuZHikI3IpMi4I9r67d7hULu3JTeTgddqaWi7ZugsO4hnTZ+oLOc5epmlCMRUUtxTj2pVrx2eHZ0cEwcH3Dl7x+/z7M5NZuPJ6SgRuWzIfhWw8TToja0ocnKxrTxMRLKeWoT+nI3o7emryk8khImCHTx++43f4G7RftILP7R7YUWTMJaE+EAmj77Y8+RI07qKYGoO530T8fvzz8Q/G32RohLv3jd6InJHLN2c2RaOEFAQkAAmYQC1JLGm2aE/TxDNX1SSSSRW6oYN1MSgR5YdLg5eOsGBHcGf3q92rRckid/xOXORigVhNtAFFJEmE7tY2DAO6rotNJLTIqpkXM1A31MkLAxfaWfi1cG1XZ9c8izMszSwJKzjyzbM1d3IrNoTqsLS8KkAILJ1Ji0n38FlwihkwxKBRFVqrhgzLLIz2jzaxtrfb2sPPh8c9yx6ocyqi0ShKSkogy3LBHZR9qX8nskYWleW7kNjYgLxjhwCjAVyNxVFRHsDC4hKXSoPWpCEtpzfHBsceZwfeP3CkMlT5vTlnoiZVg8XFRWtK+f7H4j633EJvHapSKsgihkAc8hMuY7o+jUxlJnf9y+vNrPtM98CTTY9+Gl3TkbqVwh7vHqE1Nfu/rkRFAoEG4KfRqS529LNXTp9of+zUjeU0YkoGygMFkmm5RDSVGkoXjSnlmY9Uh/XHL56Mvc2cicZQCZpqZHz9471+1vdV3ze1wWCfQ+LvdWbb0XJMwTn/IrErs0kIlAgoWm4yeb8ymJ769SPW9npbD3/wmIM5eHJWpmIO+CWGlByVH1VOWZhayp7DCS5RhXVDkNBnwzSwFln7lurmSPwNtz3L+sHZzrXtBH8BYRK7mVh8WPgAAAAASUVORK5CYII=');
		}
	}
	
}