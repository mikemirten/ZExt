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
 * @version   2.0
 */

namespace ZExt\Debug\Collectors;

use ZExt\Debug\Infosets\Infoset;

/**
 * Php engine informarion collector
 * 
 * @package    Debug
 * @subpackage Collectors
 * @author     Mike.Mirten
 * @version    2.0
 */
class Php extends CollectorAbstract {
	
	/**
	 * Opcache config options and description
	 *
	 * @var array
	 */
	static protected $opcacheConfig = [
		'enable'                  => 'Determines if Zend OPCache is enabled.',
		'enable_cli'              => 'Determines if Zend OPCache is enabled for the CLI version of PHP.',
		'memory_consumption'      => 'The OPcache shared memory storage size.',
		'interned_strings_buffer' => 'The amount of memory for interned strings in Mbytes.',
		'max_accelerated_files'   => 'The maximum number of keys (scripts) in the OPcache hash table. Only numbers between 200 and 100000 are allowed.',
		'max_wasted_percentage'   => 'The maximum percentage of "wasted" memory until a restart is scheduled.',
		'use_cwd'                 => 'When this directive is enabled, the OPcache appends the current working directory to the script key, thus eliminating possible collisions between files with the same name (basename). Disabling the directive improves performance, but may break existing applications.',
		'validate_timestamps'     => 'When disabled, you must reset the OPcache manually or restart the webserver for changes to the filesystem to take effect.',
		'revalidate_freq'         => 'How often (in seconds) to check file timestamps for changes to the shared memory storage allocation. ("1" means validate once per second, but only once per request. "0" means always validate).',
		'revalidate_path'         => 'Enables or disables file search in include_path optimization.',
		'save_comments'           => 'If disabled, all PHPDoc comments are dropped from the code to reduce the size of the optimized code.',
		'load_comments'           => 'If disabled, PHPDoc comments are not loaded from SHM, so "Doc Comments" may be always stored (save_comments=1), but not loaded by applications that don\'t need them anyway.',
		'fast_shutdown'           => 'If enabled, a fast shutdown sequence is used for the accelerated code.',
		'enable_file_override'    => 'Allow file existence override (file_exists, etc.) performance feature.',
		'optimization_level'      => 'A bitmask, where each bit enables or disables the appropriate OPcache passes.',
		'blacklist_filename'      => 'The location of the OPcache blacklist file (wildcards allowed). Each OPcache blacklist file is a text file that holds the names of files that should not be accelerated. The file format is to add each filename to a new line. The filename may be a full path or just a file prefix (i.e., /var/www/x  blacklists all the files and directories in /var/www that start with \'x\'). Line starting with a ; are ignored (comments).',
		'max_file_size'           => 'Allows exclusion of large files from being cached. By default all files are cached.',
		'consistency_checks'      => 'Check the cache checksum each N requests. The default value of "0" means that the checks are disabled.',
		'force_restart_timeout'   => 'How long to wait (in seconds) for a scheduled restart to begin if the cache is not being accessed.',
		'error_log'               => 'OPcache error_log file name. Empty string assumes "stderr".',
		'log_verbosity_level'     => 'All OPcache errors go to the Web server log. By default, only fatal errors (level 0) or errors (level 1) are logged. You can also enable warnings (level 2), info messages (level 3) or debug messages (level 4).',
		'preferred_memory_model'  => 'Preferred Shared Memory back-end. Leave empty and let the system decide.',
		'protect_memory'          => 'Protect the shared memory from unexpected writing during script execution. Useful for internal debugging only.'
	];
	
	/**
	 * Get the collected information
	 * 
	 * @return Infoset
	 */
	public function getInfo() {
		$info = $this->createInfoset()
			->setName('Php engine')
			->setIcon('elephant');
		
		$this->createTitle($info);
		$this->engineInfo($info);
		$this->extensionsInfo($info);
		
		return $info;
	}
	
	/**
	 * Create the title
	 * 
	 * @param Infoset $info
	 */
	protected function createTitle(Infoset $info) {
		preg_match('/([0-9\.]+)/i', phpversion(), $matches);
		$title = 'PHP ' . $matches[1];
		
		if (extension_loaded('xdebug')) {
			$title .= ' / [alert]xdebug[/alert]';
		}
		
		if (extension_loaded('apc')) {
			$title .= ' / [success]apc[/success]';
		}
		
		if (extension_loaded('Zend OPcache')) {
			$title .= ' / [success]opcache[/success]';
		}
		
		$info->setTitle($title);
	}
	
	/**
	 * Collect information about engine
	 * 
	 * @param Infoset $info
	 */
	protected function engineInfo(Infoset $info) {
		$engine = $this->createTable()
			->setColsWidths([1, 99])
			->enableBbCodes()
			->setTitle('Engine');
		
		$engine[] = ['PHP:', '[success]' . phpversion() . '[/success]'];
		$engine[] = ['Zend:', '[success]' . zend_version() . '[/success]'];
		$engine[] = ['OS:', php_uname()];
		$engine[] = ['User:', get_current_user()];
		
		$info[] = $engine;
	}
	
	/**
	 * Collect information about extensions
	 * 
	 * @param Infoset $info
	 */
	protected function extensionsInfo(Infoset $info) {
		$extensions = $this->createTable()
			->setHeadContent(['Name', 'Version'])
			->setColsWidths([1, 99])
			->enableBbCodes()
			->setTitle('Extensions');
		
		$info[] = $extensions;
		
		foreach (get_loaded_extensions() as $extension) {
			$version   = phpversion($extension);
			$extension = ucfirst($extension);
			
			if (empty($version)) {
				$extensions[] = [$extension, ''];
				continue;
			}
			
			$extensions[] = [$extension, '[success]' . $version . '[/success]'];
		}
		
		if (extension_loaded('Zend OPcache')) {
			$this->opcacheInfo($info);
		}
	}
	
	/**
	 * Collect information about opcache
	 * 
	 * @param Infoset $info
	 */
	protected function opcacheInfo(Infoset $info) {
		$opconf = opcache_get_configuration();
		$config = $opconf['directives'];
		
		$list = $this->createDesclist()
			->enableBbCodes()
			->setTitle('Zend OPcache ' . $opconf['version']['version']);
		
		$info[] = $list;
		
		foreach (self::$opcacheConfig as $option => $desc) {
			$option = 'opcache.' . $option;
			$value  = $config[$option];
			
			if (is_numeric($value)) {
				$value = '[alert]' . $value . '[/alert]';
			} else if (is_bool($value)) {
				$value = $value ? '[success]true[/success]' : '[info]false[/info]';
			} else if (is_string($value)) {
				$value = '[success]"' . $value . '"[/success]';
			}
			
			$list[] = [$option . ' = ' . $value, $desc];
		}
		
	}
	
}