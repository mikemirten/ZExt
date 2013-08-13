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
use ZExt\Html\ListUnordered;

class Versions extends ModuleAbstract {
	
	public function getTabIcon($size = null) {
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABGdBTUEAAK/INwWK6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9wMCBYVDI/ExucAAAQ2SURBVEjH7ZVdiFVVFMd/65x9zrn3njN3Ru+M4zjazKQzTU2jJRoJWWjSFEiEPYiR9FQEPYUP4qNUlEggERZRFCRFH4YIokahGIrolKOWozYzmvPZnRm94/3+OGf3cO9VB3oSH/3D4sDeZ63fXmvvvTbc1z1T277w3bgJAE9/Dcc2/8/02kj3pvdXta1c8MKaNuvR/afGDw6k7OliSevprJ8pnhyPc/Gva7Bl+JaL1iAyGyBbiugPLeDLh3mmfZ3Z2bDKbvRWSMytIWx7Kl3yHhSfd9e7pY+uSNBZA1GLQITCyGSh2Ht6auL8/oufcqpnNxDMzmChhhGBTee+6drQsYk6h3QJZvKQyWldyPuiUyWI59i11mZnIsJoGhwFrRHNsjmil85FbmY1e/cO9A7u3/sy57ddqwIMRsrpRLvnr3LrHRIZzXRGkypAAUSbBoRMqLX5rK/AmqayYz6ASynh+3+Qd/6Es0nRr2xsX9G8+tmvAPM2AIBPWkItUW88iZ4pCllfKGnQCJgCyoCQwXBGuFGoFLZqJuR9ODyu+WMGVq9/aA3WWz2zAc8vbxHbdJJFLfkA/ABAYwiISPkvZeLWWZy5Xgms77ByteVYXNO1JArr1r82G9DsteV9Qllf8DWIaCxDUKIhCMAoQzIRi7FsGSBSGa4YAsm8MJOD+vYHnoSXOm8BzHpvUT4QqwjaUODZgh0E2s8FaL+8RLGEjK2wALuyLWEFrgKvYggMZ2B+yC/itnQAhoIewVGNJRHIFHz/9/4zadutCS1r7TRFdABiGYIZNrEMsAxd/la2xjIEUzQ+wnCgyZYEguIkjuUQ2zZfwYtW4KiY1kLw3b49HNy41afOYPuF3jndTc3TuYCI0tQowTU1ngK3svqwKmdiK6GoYTRVhiamboxS32xRjHcpFjUpbakGfWnc5+DGD4BJSOjgwtWddY817spZkM5pltQGurtOpMHWOqqQGhtcx6DWgaGM5uN+TTqpCSuT8b6BQS6//RPuUkvhhUyQKJNj54F49Vy4c43jddkceBEu5wN6x3wZPjaSeHxR2G9vcpyEEplI+/7xuOldyZgGERPqHJ2aLonf92tvGHLZ9LmcYvJ6CdE3mUkrIF89XrayzSZXiLowloZkxObfE7/sOXRix+5D0c2tULIxSgEb3viC1rkN2BZiGTLRPxHAD33ZShzF1KtZZkb6se0n7uwherq0eGHMImfBYFJI+aC1UYShv7m5vR+AnsPziIU1sTDYhvZcQ67+NnASmKrGKV/p5PIrFIPFDH37M1AASA1ODc3kmhuXdDV2ZwxlxlMBpdO9Rxg5cOTW9Rrck2FkXh92bQdeKCKXRuPJA7vfI3HmHODfbq9VRRaocn1qqiPOI28e3bpyR9+PsdcPf240P7fidnuZJQOWdwDNgHMXb0aTF4UIYPLU2fuP773Vf5xbqxcM5iT6AAAAAElFTkSuQmCC';
	}

	public function renderTab() {
		preg_match('/([0-9\.]+)/i', phpversion(), $matches);
		
		return 'PHP ' . $matches[1];
	}
	
	public function renderPanel() {
		$extensions = array();
		
		foreach (get_loaded_extensions() as $extension) {
			$extensions[] = ucfirst($extension) . ' ' . phpversion($extension);
		}
		
		sort($extensions);
		
		$listTag = new ListUnordered($extensions, 'list-rows list-simple');
		
		return $listTag->render();
	}
	
}