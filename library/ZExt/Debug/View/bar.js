/**
 * ZExt Framework (http://z-ext.com)
 *
 * @copyright (c) 2012, Mike.Mirten
 * @license   http://www.gnu.org/licenses/gpl.html GPL License
 * @category  ZExt
 * @version   1.0
 */

(function(){
	var debug = function(){
		var elementsHolder = $('#debug-elements');
		var tabsWithPanels = $('.withpanel');
		var tabs           = $('.debug-tab');
		var panels         = $('.debug-panel-wrapper');
		var elementsWidth  = elementsHolder.width();

		$('#debug-wrapper').height(elementsHolder.height());

		var recalculateBar = function(){
			if (elementsWidth > $(window).width()) {
				tabs.addClass('minimized');
				if ($().tooltip) tabs.tooltip('enable');
			} else {
				tabs.removeClass('minimized');
				if ($().tooltip) tabs.tooltip('disable');
			}
		};

		recalculateBar();

		$(window).resize(recalculateBar);

		tabsWithPanels.click(function(){
			var panelId = $(this).attr('data-panel-id');
			var panel   = $('#' + panelId);
			var tab     = $(this);

			if (tab.hasClass('active')) {
				tab.removeClass('active');
				panel.slideUp();
			} else {
				tabsWithPanels.removeClass('active');
				panels.slideUp();

				tab.addClass('active');
				panel.slideDown();
			}
		});
	}
	
	if (typeof jQuery === 'undefined') {
		var jQueryScript = document.createElement('script');

		jQueryScript.src    = 'http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js';
		jQueryScript.type   = 'text/javascript';
		jQueryScript.onload = function(){
			$(debug);
		}
		
		document.getElementsByTagName('head')[0].appendChild(jQueryScript);
	} else {
		$(debug);
	}
})();