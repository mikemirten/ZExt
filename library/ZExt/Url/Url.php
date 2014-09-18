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

namespace ZExt\Url;

use ZExt\Components\OptionsTrait;

/**
 * URL abstraction
 * 
 * @category   ZExt
 * @package    Url
 * @author     Mike.Mirten
 * @version    1.0
 */
class Url {
	
	use OptionsTrait;
	
	/**
	 * Scheme
	 *
	 * @var string
	 */
	protected $scheme;
	
	/**
	 * Host
	 *
	 * @var string
	 */
	protected $host;
	
	/**
	 * Port
	 *
	 * @var int
	 */
	protected $port;
	
	/**
	 * Username
	 *
	 * @var string
	 */
	protected $username;
	
	/**
	 * Password
	 *
	 * @var string
	 */
	protected $password;
	
	/**
	 * Path
	 *
	 * @var string
	 */
	protected $path;
	
	/**
	 * Query
	 *
	 * @var array
	 */
	protected $query = [];
	
	/**
	 * Unparsed query
	 *
	 * @var string
	 */
	protected $queryRaw;
	
	/**
	 * Fragment
	 *
	 * @var string
	 */
	protected $fragment;
	
	/**
	 * Constructor
	 * 
	 * @param string | array $source URL string | scheme | URL params
	 */
	public function __construct($source = null) {
		if (is_string($source)) {
			if (preg_match('/^[a-z]+$/i', $source)) {
				$this->setScheme($source);
				return;
			}
			
			$this->parseFromString($source);
			return;
		}
		
		if (is_array($source) || $source instanceof \Traversable) {
			$this->setOptions($source, false, false);
		}
	}
	
	/**
	 * Parse URL from the string
	 * 
	 * @param  string $url
	 * @return Url
	 */
	public function parseFromString($url) {
		$urlData = parse_url($url);
		
		if (isset($urlData['scheme'])) {
			$this->scheme = $urlData['scheme'];
		}
		
		if (isset($urlData['host'])) {
			$this->host = $urlData['host'];
		}
		
		if (isset($urlData['port'])) {
			$this->port = $urlData['port'];
		}
		
		if (isset($urlData['user'])) {
			$this->username = $urlData['user'];
		}
		
		if (isset($urlData['pass'])) {
			$this->password = $urlData['pass'];
		}
		
		if (isset($urlData['path'])) {
			$this->path = $urlData['path'];
		}
		
		if (isset($urlData['fragment'])) {
			$this->fragment = $urlData['fragment'];
		}
		
		if (isset($urlData['query'])) {
			$this->queryRaw = $urlData['query'];
			
			parse_str($urlData['query'], $this->query);
		}
		
		return $this;
	}
	
	/**
	 * Set scheme
	 * 
	 * @param  string $scheme
	 * @return Url
	 */
	public function setScheme($scheme) {
		$this->scheme = strtolower($scheme);
		
		return $this;
	}
	
	/**
	 * Get scheme
	 * 
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}
	
	/**
	 * URL has scheme ?
	 * 
	 * @return bool
	 */
	public function hasScheme() {
		return $this->scheme !== null;
	}
	
	/**
	 * Remove scheme from URL
	 * 
	 * @return Url
	 */
	public function removeScheme() {
		$this->scheme = null;
		
		return $this;
	}
	
	/**
	 * Set username
	 * 
	 * @param  string $username
	 * @return Url
	 */
	public function setUsername($username, $password = null) {
		$this->username = (string) $username;
		
		if ($password !== null) {
			$this->setPassword($password);
		}
		
		return $this;
	}
	
	/**
	 * Get username
	 * 
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}
	
	/**
	 * URL has username ?
	 * 
	 * @return bool
	 */
	public function hasUsername() {
		return $this->username !== null;
	}
	
	/**
	 * Remove username from URL
	 */
	public function removeUsername() {
		$this->username = null;
		
		return $this;
	}

	/**
	 * Set password
	 * 
	 * @param  string $password
	 * @return Url
	 */
	public function setPassword($password) {
		$this->password = (string) $password;
		
		return $this;
	}
	
	/**
	 * Get password
	 * 
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}
	
	/**
	 * URL has password ?
	 * 
	 * @return bool
	 */
	public function hasPassword() {
		return $this->password !== null;
	}
	
	/**
	 * Remove password from URL
	 * 
	 * @return Url
	 */
	public function removePassword() {
		$this->password = null;
		
		return $this;
	}
	
	/**
	 * Set host
	 * 
	 * @param  string $host
	 * @return Url
	 */
	public function setHost($host, $port = null) {
		$this->host = (string) $host;
		
		if ($port !== null) {
			$this->setPort($port);
		}
		
		return $this;
	}
	
	/**
	 * Get host
	 * 
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}
	
	/**
	 * Host was specified ?
	 * 
	 * @return bool
	 */
	public function hasHost() {
		return $this->host !== null;
	}
	
	/**
	 * Remove host from URL
	 * 
	 * @return Url
	 */
	public function removeHost() {
		$this->host = null;
		
		return $this;
	}
	
	/**
	 * Set port
	 * 
	 * @param  int $port
	 * @return Url
	 */
	public function setPort($port) {
		$this->port = (int) $port;
		
		return $this;
	}
	
	/**
	 * Get port
	 * 
	 * @return int
	 */
	public function getPort() {
		return $this->port;
	}
	
	/**
	 * URL has port
	 * 
	 * @return bool
	 */
	public function hasPort() {
		return $this->port !== null;
	}
	
	/**
	 * Remove port from URL
	 * 
	 * @return Url
	 */
	public function removePort() {
		$this->port = null;
		
		return $this;
	}
	
	/**
	 * Set path
	 * 
	 * @param  $path
	 * @return Url
	 */
	public function setPath($path) {
		$this->path = (string) $path;
		
		return $this;
	}
	
	/**
	 * Get path
	 * 
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}
	
	/**
	 * URL has path
	 * 
	 * @return bool
	 */
	public function hasPath() {
		return $this->path !== null;
	}
	
	/**
	 * Remove path from URL
	 * 
	 * @return $this;
	 */
	public function removePath() {
		$this->path = null;
		
		return $this;
	}
	
	/**
	 * Set query raw string
	 * 
	 * @return Url
	 */
	public function setQueryRaw($query) {
		$this->queryRaw = (string) $query;
		
		return $this;
	}
	
	/**
	 * Get query raw string
	 * 
	 * @return string
	 */
	public function getQueryRaw() {
		return $this->queryRaw;
	}
	
	/**
	 * Set query parameters (overrides exists)
	 * 
	 * @param  array $params
	 * @return Url
	 */
	public function setQueryParams(array $params) {
		$this->removeQuery();
		$this->addQueryParams($params);
		
		return $this;
	}
	
	/**
	 * Add parameters to query
	 * 
	 * @param  array $params
	 * @return Url
	 */
	public function addQueryParams(array $params) {
		foreach ($params as $param => $value) {
			$this->setQueryParam($param, $value);
		}
		
		return $this;
	}
	
	/**
	 * Get query parameters
	 * 
	 * @return array
	 */
	public function getQueryParams() {
		return $this->query;
	}
	
	/**
	 * URL has query ?
	 * 
	 * @return bool
	 */
	public function hasQuery() {
		return $this->queryRaw !== null || ! empty($this->query);
	}
	
	/**
	 * Remove query from URL
	 * 
	 * @return Url
	 */
	public function removeQuery() {
		$this->query    = [];
		$this->queryRaw = null;
		
		return $this;
	}
	
	/**
	 * Set query parameter
	 * 
	 * @param  string $param
	 * @param  mixed  $value
	 * @return Url
	 */
	public function setQueryParam($param, $value) {
		$this->query[$param] = $value;
		
		return $this;
	}
	
	/**
	 * Get query parameter
	 * 
	 * @param mixed $param
	 */
	public function getQueryParam($param) {
		if (isset($this->query[$param])) {
			return $this->query[$param];
		}
	}

	/**
	 * Query has the parameter ?
	 * 
	 * @param  string $param
	 * @return bool
	 */
	public function hasQueryParam($param) {
		return isset($this->query[$param]);
	}
	
	/**
	 * Remove the param from query
	 * 
	 * @param  string $param
	 * @return Url
	 */
	public function removeQueryParam($param) {
		unset($this->query[$param]);
		
		return $this;
	}
	
	/**
	 * Set fragment
	 * 
	 * @param  string $fragment
	 * @return Url
	 */
	public function setFragment($fragment) {
		$this->fragment = (string) $fragment;
		
		return $this;
	}
	
	/**
	 * Get fragment
	 * 
	 * @return string
	 */
	public function getFragment() {
		return $this->fragment;
	}
	
	/**
	 * URL has fragment ?
	 * 
	 * @return bool
	 */
	public function hasFragment() {
		return $this->fragment !== null;
	}
	
	/**
	 * Remove fragment from URL
	 * 
	 * @return Url
	 */
	public function removeFragment() {
		$this->fragment = null;
		
		return $this;
	}
	
	/**
	 * Is URL relative ?
	 * 
	 * @return bool
	 */
	public function isRelative() {
		if ($this->host === null) {
			return true;
		}
		
		if ($this->host === '/' || $this->host === '//') {
			return true;
		}
		
		return false;
	}

	/**
	 * Assemble the URL
	 * 
	 * @return string
	 */
	public function assemble() {
		$url  = $this->assembleScheme();
		$url .= $this->assembleIdentification();
		$url .= $this->assembleHost();
		$url .= $this->assemblePath();
		$url .= $this->assembleQuery();
		$url .= $this->assembleFragment();
		
		return $url;
	}
	
	/**
	 * Assemble URL scheme
	 * 
	 * @return string
	 */
	protected function assembleScheme() {
		if ($this->scheme === null || $this->isRelative()) {
			return '';
		}
		
		return $this->scheme . '://';
	}
	
	/**
	 * Assemble URL identification
	 * 
	 * @return string
	 */
	protected function assembleIdentification() {
		if ($this->username === null) {
			return '';
		}
		
		if ($this->password === null) {
			return $this->username . '@';
		}
		
		return $this->username . ':' . $this->password . '@';
	}
	
	/**
	 * Assemble URL host
	 * 
	 * @return string
	 */
	protected function assembleHost() {
		if ($this->host === null) {
			return '/';
		}
		
		if ($this->port === null) {
			return $this->host;
		}
		
		return $this->host . ':' . $this->port;
	}
	
	/**
	 * Assemble URL path
	 * 
	 * @param  bool $hasHost
	 * @return string
	 */
	protected function assemblePath() {
		$root = $this->isRelative() ? '' : '/';
		
		if ($this->path === null) {
			return $root;
		}
		
		return $root . ltrim($this->path, '/');
	}
	
	/**
	 * Assemble URL query
	 * 
	 * @return string
	 */
	protected function assembleQuery() {
		if (empty($this->query)) {
			if ($this->queryRaw === null) {
				return '';
			}
			
			return $this->queryRaw;
		}
		
		return '?' . http_build_query($this->query);
	}
	
	/**
	 * Assemble URL fragment
	 * 
	 * @return string
	 */
	protected function assembleFragment() {
		if ($this->fragment === null) {
			return '';
		}
		
		return '#' . $this->fragment;
	}
	
	/**
	 * Set query parameter 
	 * 
	 * @param string $name
	 * @param array  $value
	 */
	public function __set($name, $value) {
		$this->setQueryParam($name, $value);
	}
	
	/**
	 * Get query paramenetr
	 * 
	 * @param mixed $name
	 */
	public function __get($name) {
		return $this->getQueryParam($name);
	}
	
	/**
	 * Query has the parameter ?
	 * 
	 * @param  string $name
	 * @return bool
	 */
	public function __isset($name) {
		return $this->hasQueryParam($name);
	}
	
	/**
	 * Remove the param from query
	 * 
	 * @param  string $name
	 */
	public function __unset($name) {
		$this->removeQueryParam($name);
	}

	/**
	 * Assemble the URL
	 * 
	 * @return string
	 */
	public function __toString() {
		return $this->assemble();
	}
	
}