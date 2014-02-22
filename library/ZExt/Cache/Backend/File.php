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

namespace ZExt\Cache\Backend;

use ZExt\Components\OptionsTrait;
use ZExt\Cache\Backend\Exceptions\OperationFailed;

use ZExt\Cache\Topology\TopologyInterface;
use ZExt\Topology\Descriptor;

/**
 * Files based backend adapter
 * 
 * @category   ZExt
 * @package    Cache
 * @subpackage Backend
 * @author     Mike.Mirten
 * @version    1.0
 */
class File implements BackendInterface, TopologyInterface {
	
	use OptionsTrait;
	
	const OPTIONS_OFFSET = 11;
	const DATA_OFFSET    = 15;
	
	const OPTION_COMPRESSED = 1;
	const OPTION_SERIALIZED = 2;
	
	/**
	 * Cache dir path
	 *
	 * @var string
	 */
	protected $path;
	
	/**
	 * Cache filenames prefix
	 *
	 * @var string
	 */
	protected $prefix = 'zcache';
	
	/**
	 * Compression using
	 *
	 * @var bool
	 */
	protected $compression = true;
	
	/**
	 * Compression treshold in bytes
	 *
	 * @var int
	 */
	protected $compressionTreshold = 1024;
	
	/**
	 * Compressin level
	 *
	 * @var int
	 */
	protected $compressionLevel = 1;
	
	/**
	 * Constructor
	 * 
	 * Parameters:
	 * param name          | datatype | default     | description
	 * ==========================================================
	 * cachePath           | string   | system temp | Path to the cache directory
	 * cachePrefix         | string   | 'zcache'    | Prefix for the cache filenames
	 * compression         | bool     | true        | Use compression of a data
	 * compressionTreshold | int      | 1024        | Compression theshold in bytes
	 * compressionLevel    | int      | 1           | Compression level 1-9 (higher -> better compression, slowly operations)
	 * 
	 * @param  string | array | Traversable $options
	 */
	public function __construct($options = null) {
		if ($options !== null) {
			if (is_string($options)) {
				$this->setCachePath($options);
			} else {
				$this->setOptions($options, false, false);
			}
		}
	}
	
	/**
	 * Fetch the data from the cache
	 * 
	 * @param  string $id
	 * @return mixed | null if no data
	 * @throws OperationFailed
	 */
	public function get($id) {
		// Open the file
		$path = $this->preparePath($id);
		$file = $this->openFile($path);
		
		if ($file === false) { // File is absent or is expired
			return false;
		}
		
		// Process the data
		$data = $this->getData($file, $path);
		
		fclose($file);
		
		return $data;
	}
	
	/**
	 * Fetch the many of the data from the cache
	 * 
	 * @param  array $id
	 * @return array
	 * @throws OperationFailed
	 */
	public function getMany(array $ids) {
		$data = [];
		
		foreach ($ids as $id) {
			$result = $this->get($id);
			
			if ($result !== false) {
				$data[$id] = $result;
			}
		}
		
		return $data;
	}
	
	/**
	 * Store the data into the cache
	 * 
	 * @param  string $id       ID of the stored data
	 * @param  mixed  $data     Stored data
	 * @param  int    $lifetime Lifetime in seconds
	 * @return bool
	 * @throws OperationFailed
	 */
	public function set($id, $data, $lifetime = 0) {
		if ($lifetime === 0) {
			$expire = '0000000000';
		} else {
			$expire = time() + $lifetime;
		}
		
		$options = 0;
		
		if (! is_string($data)) {
			$data = serialize($data);
			$options += self::OPTION_SERIALIZED;
		}
		
		if ($this->compression
		&& ($this->compressionTreshold === 0
		|| ($this->compressionTreshold > 0 && strlen($data) > $this->compressionTreshold))) {
			$data = gzcompress($data, $this->compressionLevel);
			$options += self::OPTION_COMPRESSED;
		}
		
		$options = sprintf('%03d', $options);
		
		$path   = $this->preparePath($id);
		$result = file_put_contents($this->preparePath($id), $expire . ';' . $options . ';' . $data);
		
		if ($result === false) {
			throw new OperationFailed('Unable to puth the cache content to: ' . $path);
		}
		
		return true;
	}
	
	/**
	 * Store the many of the date into the cache
	 * 
	 * @param  array $data
	 * @param  int   $lifetime
	 * @return bool
	 * @throws OperationFailed
	 */
	public function setMany(array $data, $lifetime = 0) {
		foreach ($data as $id => $value) {
			$this->set($id, $value, $lifetime);
		}
		
		return true;
	}
	
	/**
	 * Remove the data from the cache
	 * 
	 * @param  string $id
	 * @return bool
	 * @throws OperationFailed
	 */
	public function remove($id) {
		$path = $this->preparePath($id);
		
		if (is_file($path)) {
			if (unlink($path) === false) {
				throw new OperationFailed('Unable to remove the cache content: ' . $path);
			}
		}
		
		return true;
	}
	
	/**
	 * Remove the many the data from the cache
	 * 
	 * @param  array $id
	 * @return bool
	 * @throws OperationFailed
	 */
	public function removeMany(array $ids) {
		foreach ($ids as $id) {
			$this->remove($id);
		}
		
		return true;
	}

	/**
	 * Check whether the data exists in the cache
	 * 
	 * @param  string $id
	 * @return bool
	 * @throws OperationFailed
	 */
	public function has($id) {
		$path = $this->preparePath($id);
		$file = $this->openFile($path);
		
		if ($file === false) { // File is absent or is expired
			return false;
		}
		
		fclose($file);
		
		return true;
	}
	
	/**
	 * Increment the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 * @throws OperationFailed
	 */
	public function inc($id, $value = 1) {
		// Open the file
		$path = $this->preparePath($id);
		$file = $this->openFile($path, 'c+');
		
		if ($file === false) { // File is absent or is expired
			return false;
		}
		
		// Process the data
		$data = $this->getData($file, $path);
		
		if (! is_int($data)) {
			$data = (int) $data;
		}
		
		$data += $value;
		
		// Save the data
		$this->putData($file, $path, $data);
		
		return $data;
	}
	
	/**
	 * Decrement the numeric data in the cache
	 * 
	 * @param  string $id
	 * @param  int    $value
	 * @return int | bool
	 * @throws OperationFailed
	 */
	public function dec($id, $value = 1) {
		// Open the file
		$path = $this->preparePath($id);
		$file = $this->openFile($path, 'c+');
		
		if ($file === false) { // File is absent or is expired
			return false;
		}
		
		// Process the data
		$data = $this->getData($file, $path);
		
		if (! is_int($data)) {
			$data = (int) $data;
		}
		
		$data -= $value;
		
		// Save the data
		$this->putData($file, $path, $data);
		
		return $data;
	}
	
	/**
	 * Get the cache data
	 * 
	 * @param  resource $file
	 * @param  string   $path
	 * @return mixed
	 */
	protected function getData($file, $path) {
		// Options
		$options = (int) stream_get_contents($file, self::DATA_OFFSET - self::OPTIONS_OFFSET - 1, self::OPTIONS_OFFSET);
		
		if ($options === false) {
			fclose($file);
			throw new OperationFailed('Unable to get the metadata content from: ' . $path);
		}
		
		$data = stream_get_contents($file, -1, self::DATA_OFFSET);
		
		// Data
		if ($data === false) {
			fclose($file);
			throw new OperationFailed('Unable to get the cache content from: ' . $path);
		}
		
		// Decompression
		if ($options & self::OPTION_COMPRESSED) {
			$data = gzuncompress($data);
		}
		
		if ($data === false) {
			throw new OperationFailed('Unable to decompress data, file: ' . $path);
		}
		
		// Deserealization
		if ($options & self::OPTION_SERIALIZED) {
			$data = unserialize($data);
		}
		
		if ($data === false) {
			throw new OperationFailed('Unable to deserialize data, file: ' . $path);
		}
		
		return $data;
	}
	
	/**
	 * Put the cache data
	 * 
	 * @param resource $file
	 * @param strng    $path
	 * @param mixed    $data
	 */
	protected function putData($file, $path, $data) {
		fseek($file, self::DATA_OFFSET);
		
		if (fwrite($file, serialize($data)) === false) {
			fclose($file);
			throw new OperationFailed('Unable to puth the cache content to: ' . $path);
		}
		
		ftruncate($file, ftell($file));
		fclose($file);
	}
	
	/**
	 * Open the file
	 * 
	 * @param  string $path
	 * @param  string $mode
	 * @return resource | bool
	 */
	public function openFile($path, $mode = 'r') {
		// Open the file
		if (! is_file($path)) {
			return false;
		}
		
		$file = fopen($path, $mode);
		
		if ($file === false) {
			throw new OperationFailed('Unable to open the file: ' . $path);
		}
		
		// Process the expire time
		$expire = fread($file, self::OPTIONS_OFFSET - 1);
		
		if ($expire === false) {
			fclose($file);
			throw new OperationFailed('Unable to get the cache content from: ' . $path);
		}
		
		if (! preg_match('/^\d{10}$/', $expire)) {
			fclose($file);
			throw new OperationFailed('Expire time is corrupted');
		}
		
		$expire = (int) $expire;
		
		if ($expire !== 0 && $expire <= time()) {
			fclose($file);
			
			if (unlink($path) === false) {
				throw new OperationFailed('Unable to delete the expired file: ' . $path);
			}
			
			return false;
		}
		
		return $file;
	}
	
	/**
	 * Prepare the path by cache id
	 * 
	 * @param  string $id
	 * @return string
	 */
	protected function preparePath($id) {
		if (! is_scalar($id)) {
			$id = json_encode($id);
		}
		
		$filename = $this->prefix . '_' . md5($id);
		
		return $this->getCachePath() . DIRECTORY_SEPARATOR . $filename;
	}
	
	/**
	 * Set the cache path
	 * 
	 * @param  string $path
	 * @throws NoPath
	 */
	public function setCachePath($path) {
		$path = (string) $path;
		
		if (! is_dir($path)) {
			throw new NoPath('Path must be a directory');
		}
		
		if (! is_writeable($path)) {
			throw new NoPath('Path must be a writable');
		}
		
		$this->path = $path;
	}
	
	/**
	 * Get the cache path
	 * 
	 * @return string
	 * @throws NoPath
	 */
	public function getCachePath() {
		if ($this->path === null) {
			$this->path = sys_get_temp_dir();
		}
		
		return $this->path;
	}
	
	/**
	 * Set the cache filenames prefix
	 * Allowed symbols: 0-9a-z_
	 * 
	 * @param  string $prefix
	 * @throws NoPath
	 */
	public function setCachePrefix($prefix) {
		if (empty($prefix)) {
			$this->prefix = null;
			return;
		}
		
		$prefix = strtolower($prefix);
		
		if (preg_match('/[^0-9a-z_]+/', $prefix)) {
			throw new NoPath('Forbidden symbols used in the prefix');
		}
		
		$this->prefix = $prefix;
	}
	
	/**
	 * Get the cache filenames prefix
	 * 
	 * @return string
	 */
	public function getCachePrefix() {
		return $this->prefix;
	}
	
	/**
	 * Set the compression using status
	 * 
	 * @param bool $use
	 */
	public function setCompression($use) {
		$this->compression = (bool) $use;
	}
	
	/**
	 * Get the compression using status
	 * 
	 * @return bool
	 */
	public function getCompression() {
		return $this->compression;
	}
	
	/**
	 * Set the compression using treshod in bytes
	 * 
	 * @param int $size 0 for no treshold
	 */
	public function setCompressionTreshold($size) {
		$this->compressionTreshold = (int) $size;
	}
	
	/**
	 * Get the compression using treshod in bytes
	 * 
	 * @return int
	 */
	public function getCompressionTreshold() {
		return $this->compressionTreshold;
	}
	
	/**
	 * Set the compression level
	 * 
	 * @param int $level 1-9
	 */
	public function setCompressionLevel($level) {
		$level = (int) $level;
		
		if ($level < 1) {
			$level = 1;
		} else if ($level > 9) {
			$level = 9;
		}
		
		$this->compressionLevel = $level;
	}
	
	/**
	 * Get the compression level
	 * 
	 * @return int
	 */
	public function getCompressionLevel() {
		return $this->compressionLevel;
	}
	
	/**
	 * Get the cache topology
	 * 
	 * @return Descriptor
	 */
	public function getTopology() {
		$descriptor = new Descriptor('File', self::TOPOLOGY_BACKEND);
		
		$descriptor->path = $this->path;
		
		return $descriptor;
	}
	
}