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

namespace ZExt\Debug\Modules;

/**
 * Modules' abstract
 * 
 * @category   ZExt
 * @package    Debug
 * @subpackage ModuleAbstract
 * @author     Mike.Mirten
 * @version    1.0
 */
abstract class ModuleAbstract implements ModuleInterface {
	
	/**
	 * Constructor
	 * 
	 * @param array $params
	 */
	public function __construct(array $params = null) {
		if ($params !== null) {
			$this->setParameters($params);
		}
		
		$this->init();
	}
	
	/**
	 * For extensions use
	 */
	protected function init(){}
	
	/**
	 * Get a base64 encoded icon for a tab
	 * 
	 * @param  mixed size of an icon
	 * @return string 
	 */
	public function getTabIcon($size = null) {
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9wMCBYgCV2f8N4AAAPfSURBVEjHpVZPaB9FFP7e7P7yyy+ReoiphoqRJBSseCmKufbSJhcVUUQFQTwIuXhTr3rswUtPpaIeSg9K0YuHiEQEpVYwiLYKFkrABExMtDFp9re7897nYWf2T4gnB2Z3Z2b3fW++9817KwBw4YMrIydnZ4+peTfoj0LNQDOkvR7Ue4wORkkCw2Em/ZE+jQABZFkmFIFCaBTJ8xwrK1/+dfH8Ox6hyb0zp903y5++MTU5seSccyICiFQWqku4AiDDNGFhXa3qFAiI3i9r6+fPPn7qQvwkPbewMHLP+Nj8+KA/h/9oJCujDU6NTxjCMtLEAYZFAA2AiYNXE4tvRSNtj0Mza8Yk69eNrJwAURSFtJ1Li+FQitKLmqFtnWSgKtIBEAzeVkAMC/UYhKp1AXY2NyQ7yFJVQqJtQCrv2eK9AQBQCSGAMIAkCeFVuwB393YlL/JkP8v9+uZOcXJ6aszCbiyQHWmxdiwCRUbCrPLfSJTey3Mvvzqz8PTz89e/v341mX3ksdET03OLny+vXDl+38SxEw9MPuSVUBJm0YCFcZizqB6r5gioVjva2t7l4uK5l0b6ow9f+vDyJ+nW+lr20fsX3/72i8/+PPvdzWdKr1CyI0miAbIYEBHAKkcY4kSvnJ2emkuTFL//sf2bei/prRur/taN1TUAiTd1pWrFL4nSG7wavFf4FufGqgsA5xwSJ3BOAEJIILcSReldkQ8lbcXDlYW6LC9ReA1bZ5BjE8j2nBph4V2ScE4CoEPpvZhpBwB5Wbis8I3RlhxrgBZwDDADfV4rqgTAcFg4M0NnB770QhKqWkvT2Gjd2JyDjnFaoA0wGkAgL3JnZdHZAUvvndcmkG1q2oa7zxbUBkR5qxJlWbpyeCBu8dkXB0tvvvsUgAnvvUQKYhKz0NWsocasFkKUMmkgybtZCZc4qPfO1CNd/fHn/iuvvf7WqUev8uDu/riRULUWLSFJhCNOWi1bHhKCuESuXft6bWv7zsbx+yeLJHVIaSo7f+/mT8w/eWb3nz2U3hOA1HrHUXE4RFMAEioGg7E7l99bemHm9JkHe2nvIK3yC+XgYIhe2qsDFzNqfG5zzxZIoyiDwMU47Nxe/WqjSteqolal6/BhJ1m181EMYn1yD8k1SQA1E1RKDfUgBLAqk0HE7Hrf3NE9fIeAKjvsFKuUJEybwMbUySPSdJyrs611D6OAdY2oAVRVi9Ln2bAA2zU4GO7c61SNuoI1JxsQJyjKYgjU1RXp3uba/vLy8qUffvp1B2Ba7+BwyTyq8MSyKdWaiOPtm6sfAyjqv4qYJgD0w/3/NAvGNU78C3UNDL/yP6wmAAAAAElFTkSuQmCC';
	}
	
	/**
	 * Set module's parameters 
	 * Calls set{"ParameterName"} methods
	 * 
	 * @param array $params
	 * @return ModuleAbstract
	 */
	public function setParameters(array $params) {
		foreach ($params as $param => $value) {
			$method = 'set' . ucfirst($param);
			
			// Recursion protection
			if ($method === 'setParameters') {
				continue;
			}
			
			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}
		
		return $this;
	}
	
	/**
	 * Title of a tab
	 * 
	 * @reuturn string | null
	 */
	public function getTitle() {
		
	}
	
	/**
	 * Panel with full information
	 * 
	 * @return string | null if nothing, to show
	 */
	public function renderPanel() {
		
	}
	
	/**
	 * Format a time value
	 * 
	 * @param  int | float $seconds Time in seconds
	 * @return string
	 */
	function formatTime($seconds) {
		switch (true) {
			case $seconds == 0:
				return 0;
			
			case $seconds < 0.01:
				$time = round($seconds * 1000, 2) . 'ms';
				break;
			
			case $seconds < 0.1:
				$time = round($seconds * 1000, 1) . 'ms';
				break;
			
			case $seconds < 1:
				$time = round($seconds * 1000) . 'ms';
				break;
			
			case $seconds < 10:
				$time = round($seconds, 2) . 's';
				break;
			
			default:
				$time = round($seconds, 1) . 's';
		}
		
		return $time;
	}
	
	/**
	 * Format a memory value
	 * 
	 * @param  int $memory
	 * @return string
	 */
	function formatMemory($memory) {
		switch (true) {
			case $memory == 0:
				return 0;
			
			case $memory < 1024:
				$memory = round($memory) . 'b';
				break;
			
			case $memory < 1048576:
				$memory = round($memory / 1024, 2) . 'K';
				break;
			
			case $memory < 10485760:
				$memory = round($memory / 1024, 1) . 'K';
				break;
			
			case $memory < 104857600:
				$memory = round($memory / 1024) . 'K';
				break;
			
			case $memory < 1073741824:
				$memory = round($memory / 1048576, 1) . 'M';
				break;
			
			default:
				$memory = round($memory / 1048576) . 'M';
		}
		
		return $memory;
	}
	
}