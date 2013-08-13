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

class Time extends ModuleAbstract {
	
	public function getTabIcon($size = null) {
		return 'data:image/png;base64,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';
	}
	
	public function renderTab() {
		$elapsed = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
		
		return $this->formatTime($elapsed);
	}
	
}