/**
 * ZExt Framework (http://z-ext.com)
 *
 * @copyright (c) 2012, Mike.Mirten
 * @license   http://www.gnu.org/licenses/gpl.html GPL License
 * @category  ZExt
 * @version   1.0
 */

(function(){
	var debugLoad = function(){
		if (! $('#debug-wrapper')[0]) {
			var wrapper = $('<div id="debug-wrapper"></div>').css('opacity', 0);
			$('body').append(wrapper);
		} else {
			var wrapper = $('#debug-wrapper');
			wrapper.animate({opacity: 0});
		}

		$.get('$url', function(result){
			wrapper.html(result).animate({opacity: 1});
		});
	}

	if (typeof jQuery === 'undefined') {
		var jQueryScript = document.createElement('script');

		jQueryScript.src    = 'http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js';
		jQueryScript.type   = 'text/javascript';
		jQueryScript.onload = debugLoad;

		document.getElementsByTagName('head')[0].appendChild(jQueryScript);
	} else {
		debugLoad();
	}
})();