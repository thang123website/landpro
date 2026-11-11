jQuery(function ($) {
	var lastHeight = 0, curHeight = 0, $frame = $('.wp_thuocloban_iframe');
	setInterval(function () {
		curHeight = $frame.contents().find('body').outerHeight();
		if (curHeight != lastHeight) {
			$frame.css('height', (lastHeight = curHeight) + 'px');
		}
	}, 500);
});